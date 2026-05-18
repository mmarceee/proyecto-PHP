<?php

return [
    'accepted' => 'El campo :attribute debe ser aceptado.',
    'confirmed' => 'La confirmación de :attribute no coincide.',
    'email' => 'El campo :attribute debe ser un correo electrónico válido.',
    'in' => 'El campo :attribute seleccionado no es válido.',
    'max' => [
        'string' => 'El campo :attribute no debe superar los :max caracteres.',
    ],
    'min' => [
        'string' => 'El campo :attribute debe tener al menos :min caracteres.',
    ],
    'required' => 'El campo :attribute es obligatorio.',
    'required_if' => 'El campo :attribute es obligatorio cuando :other es :value.',
    'string' => 'El campo :attribute debe ser texto.',
    'unique' => 'El valor del campo :attribute ya está en uso.',

    'custom' => [
        'password' => [
            'confirmed' => 'Las contraseñas no coinciden.',
        ],
        'especialidad' => [
            'required_if' => 'La especialidad es obligatoria si te registrás como profesional.',
        ],
        'descripcion' => [
            'required_if' => 'La descripción profesional es obligatoria si te registrás como profesional.',
        ],
    ],

    'telefono' => [
        'regex' => 'El teléfono debe contener solo números.',
    ],

    'attributes' => [
        'name' => 'nombre',
        'apellido' => 'apellido',
        'email' => 'correo electrónico',
        'telefono' => 'teléfono',
        'password' => 'contraseña',
        'password_confirmation' => 'confirmación de contraseña',
        'tipo_registro' => 'tipo de registro',
        'descripcion' => 'descripción profesional',
        'especialidad' => 'especialidad',
        'nombre_comercial' => 'nombre comercial',
        'telefono' => 'teléfono',
    ],
];