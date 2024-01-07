<?php

namespace Database\Seeders;

use App\Models\Master\GoodsType;
use Illuminate\Database\Seeder;

class GoodsTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    protected $goods_type = [
        ['goods_type_name' => 'Timber/Plywood/Laminate',
            'active' => 1,
        ],
        ['goods_type_name' => 'Electrical/Electronics/Home Appliances',
            'active' => 1,
        ],
        ['goods_type_name' => 'Building/Construction',
            'active' => 1,
        ],
        ['goods_type_name' => 'Catering/Restaurant/Event Management',
            'active' => 1,
        ],
        ['goods_type_name' => 'Machines/Equipments/Spare Parts/Metals',
            'active' => 1,
        ],
        ['goods_type_name' => 'Textile/Garments/Fashion Accessories',
            'active' => 1,
        ],
        ['goods_type_name' => 'Furniture/Home Furnishing',
            'active' => 1,
        ],
        ['goods_type_name' => 'House Shifting',
            'active' => 1,
        ],
        ['goods_type_name' => 'Ceramics/Sanitaryware/HardWare',
            'active' => 1,
        ],
        ['goods_type_name' => 'Paper/Packaging/Printed Material',
            'active' => 1,
        ],
        ['goods_type_name' => 'Chemicals/Paints',
            'active' => 1,
        ],
        ['goods_type_name' => 'Logistics service provider/Packers and Movers',
            'active' => 1,
        ],
        ['goods_type_name' => 'Perishable Food Items',
            'active' => 1,
        ],
        ['goods_type_name' => 'Pharmacy/Medical?Healthcare/Fitness Equipment',
            'active' => 1,
        ],
        ['goods_type_name' => 'FMCG/Food Products',
            'active' => 1,
        ],
        ['goods_type_name' => 'Plastic/Rubber',
            'active' => 1,
        ],
        ['goods_type_name' => 'Books/Stationery/Toys/Gifts',
            'active' => 1,
        ]
    ];
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $created_params = $this->goods_type;

        $value = GoodsType::first();
        if(is_null($value))
        {
          foreach ($created_params as $goods) 
          {
            GoodsType::create($goods);
          }
        }else {
          foreach ($created_params as $goods) 
          {
            $value->update($goods);
          }
        }

    }
}
