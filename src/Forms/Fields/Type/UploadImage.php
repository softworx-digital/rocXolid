<?php

namespace Softworx\RocXolid\Forms\Fields\Type;

use Softworx\RocXolid\Forms\Contracts\FormField;
use Softworx\RocXolid\Forms\Fields\Type\UploadFile;

class UploadImage extends UploadFile
{
    protected $default_options = [
        'type-template' => 'upload-image',
        // multiple
        'multiple' => false,
        // field wrapper
        'wrapper' => false,
        // component helper classes
        'helper-classes' => [
            'error-class' => 'has-error',
            'success-class' => 'has-success',
        ],
        // field label
        'label' => false,
        // upload url
        'upload-url' => null,
        // image preview
        'image-preview' => true,
        // image preview size
        'image-preview-size' => 'small',
        // field HTML attributes
        'attributes' => [
            'accept' => 'image',
            // 'maxsize' => '5242880', // 5 MB
        ],
    ];

    protected function setImagePreview($preview): FormField
    {
        return $this->setComponentOptions('image-preview', $preview);
    }

    protected function setImagePreviewSize($preview_size): FormField
    {
        return $this->setComponentOptions('image-preview-size', $preview_size);
    }
}
