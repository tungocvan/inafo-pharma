<?php

return array (
  'name' => 'Pharma',
  'type' => 'domain',
  'enabled' => true,
  'permissions' => [
        'view_pharma',
        'create_pharma',
        'edit_pharma',
        'delete_pharma',
    ],
  'tables' =>
  array (
    0 => 'pharma_medicines',
    1 => 'pharma_drug_bid_awards',
    2 => 'pharma_supplier_trackings',
  ),
);
