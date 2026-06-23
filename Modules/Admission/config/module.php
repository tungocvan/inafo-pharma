<?php

return array (
  'name' => 'Admission',
  'type' => 'domain',
  'enabled' => true,
  'enable_pdf_convert' => false,
  'permissions' => [
        'view_admission',
        'create_admission',
        'edit_admission',
        'delete_admission',
        'import_admission',
        'export_admission',
        'approve_admission',
        'reject_admission',
        'download_admission_documents',
        'manage_admission_locations',
    ],
  'tables' =>
  array (
    0 => 'admission_locations',
    1 => 'admission_applications',
    2 => 'admission_catalogs',
  ),
);
