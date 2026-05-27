<?php

return array (
  'name' => 'Admission',
  'type' => 'domain',
  'enabled' => false,
  'enable_pdf_convert' => false,
  'permissions' => [
        'view_admission',
        'create_admission',
        'edit_admission',
        'delete_admission',
    ],
  'tables' =>
  array (
    0 => 'admission_locations',
    1 => 'admission_applications',
    2 => 'admission_catalogs',
  ),
);
