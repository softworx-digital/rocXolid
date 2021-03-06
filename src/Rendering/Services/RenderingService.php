<?php

namespace Softworx\RocXolid\Rendering\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\View as IlluminateView;
use Illuminate\View\Factory as IlluminateViewFactory;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Symfony\Component\Debug\Exception\FatalThrowableError;
// rocXolid rendering contracts
use Softworx\RocXolid\Rendering\Contracts\Renderable;
// rocXolid rendering exceptions
use Softworx\RocXolid\Rendering\Exceptions\ViewNotFoundException;

/**
 * Retrieves view for given object and view name.
 *
 * @author softworx <hello@softworx.digital>
 * @package Softworx\RocXolid
 * @version 1.0.0
 */
class RenderingService implements Contracts\RenderingService
{
    /**
     * @param array
     */
    protected static $fallback_view_packages = [
        'rocXolid',
    ];

    /**
     * @param array
     */
    protected static $fallback_view_dirs = [
        '_generic',
    ];

    /**
     * View not found exception view.
     *
     * @var string
     */
    protected static $not_found_view_path = 'rocXolid::not-found';

    /**
     * Left here for possible experiments...
     * Should represent some config of how to find the view path.
     *
     * @param array
     */
    protected static $preference = [
        'getViewPackages',
        'getViewDirectories',
    ];

    /**
     * @var \Illuminate\Contracts\Cache\Repository
     */
    protected $cache;

    /**
     * Constructor.
     *
     * @param \Illuminate\Contracts\Cache\Repository $cache Cache storage.
     */
    public function __construct(CacheRepository $cache)
    {
        $this->cache = $cache;
    }

    /**
     * {@inheritDoc}
     */
    public static function renderComponent(Renderable $component, string $view_name = null, array $assignments = [])
    {
        echo $component->render($view_name, $assignments);
    }

    /**
     * {@inheritDoc}
     */
    public static function render(string $content, array $data = []): string
    {
        $data['__env'] = app(IlluminateViewFactory::class);

        $php = Blade::compileString($content);

        $ob_level = ob_get_level();

        ob_start();
        extract($data, EXTR_SKIP);

        try {
            eval('?>' . $php);
        } catch (\Throwable $e) {
            while (ob_get_level() > $ob_level) {
                ob_end_clean();
            }

            logger()->error($e);

            throw $e;
        } catch (\Throwable $e) {
            while (ob_get_level() > $ob_level) {
                ob_end_clean();
            }

            throw new FatalThrowableError($e);
        }

        return ob_get_clean();
    }

    /**
     * {@inheritDoc}
     */
    public function getView(Renderable $component, string $view_name, array $assignments = []): IlluminateView
    {
        try {
            return View::make($this->getViewPath($component, $view_name), $assignments);
        } catch (ViewNotFoundException $e) {
            return View::make($this->getNotFoundViewPath(), [ 'e' => $e ]);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getViewPath(Renderable $component, string $view_name): string
    {
        $cache_key = $component->getPackageViewCacheKey($view_name);

        if ($component->useRenderingCache() && $this->cache->has($cache_key)) {
            return $this->cache->get($cache_key);
        }

        $hierarchy = $this->getHierarchy($component);

        $search_paths = collect();

        // looks better than using Collection::each()
        foreach ($this->getViewPackages($component, $hierarchy) as $view_package) {
            foreach ($this->getViewDirectories($component, $hierarchy) as $view_dir) {
                foreach ($component->composePackageViewPaths($view_package, $view_dir, $view_name) as $candidate) {
                    $search_paths->push($candidate);

                    if (View::exists($candidate)) {
                        if ($component->useRenderingCache()) {
                            $this->cache->put($cache_key, $candidate);
                        }

                        return $candidate;
                    }
                }
            }
        }

        /*
         * this is kinda experimentory, to find out if it is possible to create the iterations dynamically
         * maybe with the use of Collection::pipe()
        collect(static::$preference)->each(function ($method) use ($component, $hierarchy) {
            $this->$method($component, $hierarchy)
            ...
        });
         */

        throw new ViewNotFoundException($component, $view_name, $search_paths);
    }

    /**
     * Get view - placeholder path for not found templates.
     *
     * @return string
     */
    protected function getNotFoundViewPath(): string
    {
        return static::$not_found_view_path;
    }

    /**
     * Create hierarchical collection of components classes for future use.
     * Start with given component and add its eligible parents.
     *
     * @param \Softworx\RocXolid\Rendering\Contracts\Renderable $component Component at the hierarchy top.
     * @return \Illuminate\Support\Collection
     */
    protected function getHierarchy(Renderable $component): Collection
    {
        $reflection = new \ReflectionClass($component);

        $hierarchy = collect();

        do {
            // skip abstract components
            if (!$reflection->isInstantiable()) {
                continue;
            }

            $hierarchy->push([
                'type' => $reflection->getName(),
                'dir' => $this->getClassNameViewDirectory($reflection),
            ]);
        } while (
            ($reflection = $reflection->getParentClass())
            // && $reflection->isInstantiable() // don't break at abstract components
            && $reflection->implementsInterface(Renderable::class)
        );

        return $hierarchy;
    }

    /**
     * Create priority collection of view packages to look for the view.
     *
     * @param \Softworx\RocXolid\Rendering\Contracts\Renderable $component Component being rendered.
     * @param \Illuminate\Support\Collection $hierarchy Component hierarchy.
     * @return \Illuminate\Support\Collection
     */
    protected function getViewPackages(Renderable $component, Collection $hierarchy): Collection
    {
        $hierarchy_view_packages = $hierarchy->pluck('type')->map(function ($type) {
            return app($type)->getViewPackage();
        })->toArray();

        return collect(array_merge(
            [ $component->getViewPackage() ],
            $hierarchy_view_packages,
            static::$fallback_view_packages
        ))->unique();
    }

    /**
     * Create priority collection of view directories inside package to look for the view.
     *
     * @param \Softworx\RocXolid\Rendering\Contracts\Renderable $component Component being rendered.
     * @param \Illuminate\Support\Collection $hierarchy Component hierarchy.
     * @return \Illuminate\Support\Collection
     */
    protected function getViewDirectories(Renderable $component, Collection $hierarchy): Collection
    {
        $hierarchy_view_dirs = $hierarchy->pluck('dir')->toArray();

        return collect(array_merge(
            [ $component->getViewDirectory() ],
            $hierarchy_view_dirs,
            static::$fallback_view_dirs
        ))->filter()->unique();
    }

    /**
     * Create directory path based on component's fully qualified name.
     * Takes the namespace parts in the Components sub-namespace and consider them as directories (in kebab case).
     *
     * @param \ReflectionClass $reflection Component's reflection.
     * @return string
     */
    protected function getClassNameViewDirectory(\ReflectionClass $reflection): string
    {
        $path = Str::after($reflection->getName(), 'Components\\');
        $path = collect(explode('\\', $path));

        return $path->map(function ($dir) {
            return Str::kebab($dir);
        })->implode('.');
    }
}
