<?php

class imagesSizes {

    public const THEME_DEFAULT = 1;
    public const THEME_FASHION = 2;
    public const THEME_HEAVY_EQUIPMENT = 3;
    public const THEME_AUTOMOBILE = 4;

    public static function getBannersDimensions(): array {
        return [
            static::THEME_DEFAULT => [
                Collections::TYPE_BANNER_LAYOUT1 => [
                    applicationConstants::SCREEN_DESKTOP => [
                        'width' => 1350,
                        'height' => 405,
						'defaultImage' => 'banner-big-default.png'
                    ],
                    applicationConstants::SCREEN_IPAD => [
                        'width' => 1024,
                        'height' => 307,
						'defaultImage' => 'banner-big-default.png'
                    ],
                    applicationConstants::SCREEN_MOBILE => [
                        'width' => 640,
                        'height' => 360,
						'defaultImage' => 'banner-big-default.png'
                    ],
                ],
                Collections::TYPE_BANNER_LAYOUT2 => [
                    applicationConstants::SCREEN_DESKTOP => [
                        'width' => 660,
                        'height' => 198,
						'defaultImage' => 'banner-big-default.png'
                    ],
                    applicationConstants::SCREEN_IPAD => [
                        'width' => 660,
                        'height' => 198,
						'defaultImage' => 'banner-big-default.png'
                    ],
                    applicationConstants::SCREEN_MOBILE => [
                        'width' => 640,
                        'height' => 360,
						'defaultImage' => 'banner-big-default.png'
                    ],
                ],
                Collections::TYPE_BANNER_LAYOUT3 => [
                    applicationConstants::SCREEN_DESKTOP => [
                        'width' => 600,
                        'height' => 338,
						'defaultImage' => 'banner-big-default.png'
                    ],
                    applicationConstants::SCREEN_IPAD => [
                        'width' => 660,
                        'height' => 198,
						'defaultImage' => 'banner-big-default.png'
                    ],
                    applicationConstants::SCREEN_MOBILE => [
                        'width' => 640,
                        'height' => 360,
						'defaultImage' => 'banner-big-default.png'
                    ],
                ],
                Collections::TYPE_BANNER_LAYOUT4 => [
                    Collections::BANNER_POSITION_LEFT => [
                        applicationConstants::SCREEN_DESKTOP => [
                            'width' => 1050,
                            'height' => 700,
							'defaultImage' => 'banner-big-default.png'
                        ],
                        applicationConstants::SCREEN_IPAD => [
                            'width' => 1050,
                            'height' => 700,
							'defaultImage' => 'banner-big-default.png'
                        ],
                        applicationConstants::SCREEN_MOBILE => [
                            'width' => 1050,
                            'height' => 700,
							'defaultImage' => 'banner-big-default.png'
                        ],
                    ],
                    Collections::BANNER_POSITION_RIGHT => [
                        applicationConstants::SCREEN_DESKTOP => [
                            'width' => 510,
                            'height' => 700,
							'defaultImage' => 'banner-small-default.png'
                        ],
                        applicationConstants::SCREEN_IPAD => [
                            'width' => 510,
                            'height' => 700,
							'defaultImage' => 'banner-small-default.png'
                        ],
                        applicationConstants::SCREEN_MOBILE => [
                            'width' => 510,
                            'height' => 700,
							'defaultImage' => 'banner-small-default.png'
                        ],
                    ]
                ],
                Collections::TYPE_BANNER_LAYOUT5 => [
                    applicationConstants::SCREEN_DESKTOP => [
                        'width' => 1050,
                        'height' => 700,
						'defaultImage' => 'banner-big-default.png'
                    ],
                    applicationConstants::SCREEN_IPAD => [
                        'width' => 1050,
                        'height' => 700,
						'defaultImage' => 'banner-big-default.png'
                    ],
                    applicationConstants::SCREEN_MOBILE => [
                        'width' => 1050,
                        'height' => 700,
						'defaultImage' => 'banner-big-default.png'
                    ],
                ],
                
                Collections::TYPE_BANNER_LAYOUT_DETAIL => [
                    applicationConstants::SCREEN_DESKTOP => [
                        'width' => 1920,
                        'height' => 366,
						'defaultImage' => 'banner-big-default.png'
                    ],
                    applicationConstants::SCREEN_IPAD => [
                        'width' => 1920,
                        'height' => 366,
						'defaultImage' => 'banner-big-default.png'
                    ],
                    applicationConstants::SCREEN_MOBILE => [
                        'width' => 1920,
                        'height' => 366,
						'defaultImage' => 'banner-big-default.png'
                    ],
                ]
                
            ],
            static::THEME_FASHION => [
                Collections::TYPE_BANNER_LAYOUT1 => [
                    applicationConstants::SCREEN_DESKTOP => [
                        'width' => 1350,
                        'height' => 405,
						'defaultImage' => 'banner-big-default.png'
                    ],
                    applicationConstants::SCREEN_IPAD => [
                        'width' => 1024,
                        'height' => 307,
						'defaultImage' => 'banner-big-default.png'
                    ],
                    applicationConstants::SCREEN_MOBILE => [
                        'width' => 640,
                        'height' => 360,
						'defaultImage' => 'banner-big-default.png'
                    ],
                ],
                Collections::TYPE_BANNER_LAYOUT2 => [
                    applicationConstants::SCREEN_DESKTOP => [
                        'width' => 660,
                        'height' => 198,
						'defaultImage' => 'banner-big-default.png'
                    ],
                    applicationConstants::SCREEN_IPAD => [
                        'width' => 660,
                        'height' => 198,
						'defaultImage' => 'banner-big-default.png'
                    ],
                    applicationConstants::SCREEN_MOBILE => [
                        'width' => 640,
                        'height' => 360,
						'defaultImage' => 'banner-big-default.png'
                    ],
                ],
                Collections::TYPE_BANNER_LAYOUT3 => [
                    applicationConstants::SCREEN_DESKTOP => [
                        'width' => 600,
                        'height' => 338,
						'defaultImage' => 'banner-big-default.png'
                    ],
                    applicationConstants::SCREEN_IPAD => [
                        'width' => 660,
                        'height' => 198,
						'defaultImage' => 'banner-big-default.png'
                    ],
                    applicationConstants::SCREEN_MOBILE => [
                        'width' => 640,
                        'height' => 360,
						'defaultImage' => 'banner-big-default.png'
                    ],
                ],
                Collections::TYPE_BANNER_LAYOUT4 => [
                    Collections::BANNER_POSITION_LEFT => [
                        applicationConstants::SCREEN_DESKTOP => [
                            'width' => 1050,
                            'height' => 700,
							'defaultImage' => 'banner-big-default.png'
                        ],
                        applicationConstants::SCREEN_IPAD => [
                            'width' => 1050,
                            'height' => 700,
							'defaultImage' => 'banner-big-default.png'
                        ],
                        applicationConstants::SCREEN_MOBILE => [
                            'width' => 1050,
                            'height' => 700,
							'defaultImage' => 'banner-big-default.png'
                        ],
                    ],
                    Collections::BANNER_POSITION_RIGHT => [
                        applicationConstants::SCREEN_DESKTOP => [
                            'width' => 510,
                            'height' => 700,
							'defaultImage' => 'banner-small-default.png'
                        ],
                        applicationConstants::SCREEN_IPAD => [
                            'width' => 510,
                            'height' => 700,
							'defaultImage' => 'banner-small-default.png'
                        ],
                        applicationConstants::SCREEN_MOBILE => [
                            'width' => 510,
                            'height' => 700,
							'defaultImage' => 'banner-small-default.png'
                        ],
                    ]
                ],
                Collections::TYPE_BANNER_LAYOUT5 => [
                    applicationConstants::SCREEN_DESKTOP => [
                        'width' => 1050,
                        'height' => 700,
						'defaultImage' => 'banner-big-default.png'
                    ],
                    applicationConstants::SCREEN_IPAD => [
                        'width' => 1050,
                        'height' => 700,
						'defaultImage' => 'banner-big-default.png'
                    ],
                    applicationConstants::SCREEN_MOBILE => [
                        'width' => 1050,
                        'height' => 700,
						'defaultImage' => 'banner-big-default.png'
                    ],
                ],
                Collections::TYPE_BANNER_LAYOUT_DETAIL => [
                    applicationConstants::SCREEN_DESKTOP => [
                        'width' => 1920,
                        'height' => 366,
						'defaultImage' => 'banner-big-default.png'
                    ],
                    applicationConstants::SCREEN_IPAD => [
                        'width' => 1920,
                        'height' => 366,
						'defaultImage' => 'banner-big-default.png'
                    ],
                    applicationConstants::SCREEN_MOBILE => [
                        'width' => 1920,
                        'height' => 366,
						'defaultImage' => 'banner-big-default.png'
                    ],
                ]
            ],
            static::THEME_HEAVY_EQUIPMENT => [
                Collections::TYPE_BANNER_LAYOUT1 => [
                    applicationConstants::SCREEN_DESKTOP => [
                        'width' => 1350,
                        'height' => 405,
						'defaultImage' => 'banner-big-default.png'
                    ],
                    applicationConstants::SCREEN_IPAD => [
                        'width' => 1024,
                        'height' => 307,
						'defaultImage' => 'banner-big-default.png'
                    ],
                    applicationConstants::SCREEN_MOBILE => [
                        'width' => 640,
                        'height' => 360,
						'defaultImage' => 'banner-big-default.png'
                    ],
                ],
                Collections::TYPE_BANNER_LAYOUT2 => [
                    applicationConstants::SCREEN_DESKTOP => [
                        'width' => 660,
                        'height' => 198,
						'defaultImage' => 'banner-big-default.png'
                    ],
                    applicationConstants::SCREEN_IPAD => [
                        'width' => 660,
                        'height' => 198,
						'defaultImage' => 'banner-big-default.png'
                    ],
                    applicationConstants::SCREEN_MOBILE => [
                        'width' => 640,
                        'height' => 360,
						'defaultImage' => 'banner-big-default.png'
                    ],
                ],
                Collections::TYPE_BANNER_LAYOUT3 => [
                    applicationConstants::SCREEN_DESKTOP => [
                        'width' => 600,
                        'height' => 338,
						'defaultImage' => 'banner-big-default.png'
                    ],
                    applicationConstants::SCREEN_IPAD => [
                        'width' => 660,
                        'height' => 198,
						'defaultImage' => 'banner-big-default.png'
                    ],
                    applicationConstants::SCREEN_MOBILE => [
                        'width' => 640,
                        'height' => 360,
						'defaultImage' => 'banner-big-default.png'
                    ],
                ],
                Collections::TYPE_BANNER_LAYOUT4 => [
                    Collections::BANNER_POSITION_LEFT => [
                        applicationConstants::SCREEN_DESKTOP => [
                            'width' => 1050,
                            'height' => 525,
							'defaultImage' => 'banner-big-default.png'
                        ],
                        applicationConstants::SCREEN_IPAD => [
                            'width' => 1050,
                            'height' => 525,
							'defaultImage' => 'banner-big-default.png'
                        ],
                        applicationConstants::SCREEN_MOBILE => [
                            'width' => 1050,
                            'height' => 525,
							'defaultImage' => 'banner-big-default.png'
                        ],
                    ],
                    Collections::BANNER_POSITION_RIGHT => [
                        applicationConstants::SCREEN_DESKTOP => [
                            'width' => 525,
                            'height' => 525,
							'defaultImage' => 'banner-small-default.png'
                        ],
                        applicationConstants::SCREEN_IPAD => [
                            'width' => 525,
                            'height' => 525,
							'defaultImage' => 'banner-small-default.png'
                        ],
                        applicationConstants::SCREEN_MOBILE => [
                            'width' => 525,
                            'height' => 525,
							'defaultImage' => 'banner-small-default.png'
                        ],
                    ]
                ],
                Collections::TYPE_BANNER_LAYOUT5 => [
                    applicationConstants::SCREEN_DESKTOP => [
                        'width' => 1050,
                        'height' => 700,
						'defaultImage' => 'banner-big-default.png'
                    ],
                    applicationConstants::SCREEN_IPAD => [
                        'width' => 1050,
                        'height' => 700,
						'defaultImage' => 'banner-big-default.png'
                    ],
                    applicationConstants::SCREEN_MOBILE => [
                        'width' => 1050,
                        'height' => 700,
						'defaultImage' => 'banner-big-default.png'
                    ],
                ],
                Collections::TYPE_BANNER_LAYOUT_DETAIL => [
                    applicationConstants::SCREEN_DESKTOP => [
                        'width' => 2000,
                        'height' => 500,
						'defaultImage' => 'banner-big-default.png'
                    ],
                    applicationConstants::SCREEN_IPAD => [
                        'width' => 2000,
                        'height' => 500,
						'defaultImage' => 'banner-big-default.png'
                    ],
                    applicationConstants::SCREEN_MOBILE => [
                        'width' => 2000,
                        'height' => 500,
						'defaultImage' => 'banner-big-default.png'
                    ],
                ],
                
                Collections::TYPE_BLOG_LAYOUT1 => [
                    applicationConstants::SCREEN_DESKTOP => [
                        'width' => 908,
                        'height' => 681,
						'defaultImage' => 'banner-big-default.png'
                    ],
                    applicationConstants::SCREEN_IPAD => [
                        'width' => 908,
                        'height' => 681,
						'defaultImage' => 'banner-big-default.png'
                    ],
                    applicationConstants::SCREEN_MOBILE => [
                        'width' => 908,
                        'height' => 681,
						'defaultImage' => 'banner-big-default.png'
                    ],
                ]
                
                
            ],
			static::THEME_AUTOMOBILE => [
                Collections::TYPE_BANNER_LAYOUT1 => [
                    applicationConstants::SCREEN_DESKTOP => [
                        'width' => 1344,
                        'height' => 576,
						'defaultImage' => 'banner-big-default.png'
                    ],
                    applicationConstants::SCREEN_IPAD => [
                        'width' => 1024,
                        'height' => 307,
						'defaultImage' => 'banner-big-default.png'
                    ],
                    applicationConstants::SCREEN_MOBILE => [
                        'width' => 640,
                        'height' => 360,
						'defaultImage' => 'banner-big-default.png'
                    ],
                ],
                Collections::TYPE_BANNER_LAYOUT2 => [
                    applicationConstants::SCREEN_DESKTOP => [
                        'width' => 660,
                        'height' => 198,
						'defaultImage' => 'banner-big-default.png'
                    ],
                    applicationConstants::SCREEN_IPAD => [
                        'width' => 660,
                        'height' => 198,
						'defaultImage' => 'banner-big-default.png'
                    ],
                    applicationConstants::SCREEN_MOBILE => [
                        'width' => 640,
                        'height' => 360,
						'defaultImage' => 'banner-big-default.png'
                    ],
                ],
                Collections::TYPE_BANNER_LAYOUT3 => [
                    applicationConstants::SCREEN_DESKTOP => [
                        'width' => 600,
                        'height' => 338,
						'defaultImage' => 'banner-big-default.png'
                    ],
                    applicationConstants::SCREEN_IPAD => [
                        'width' => 660,
                        'height' => 198,
						'defaultImage' => 'banner-big-default.png'
                    ],
                    applicationConstants::SCREEN_MOBILE => [
                        'width' => 640,
                        'height' => 360,
						'defaultImage' => 'banner-big-default.png'
                    ],
                ],
                Collections::TYPE_BANNER_LAYOUT4 => [
                    Collections::BANNER_POSITION_LEFT => [
                        applicationConstants::SCREEN_DESKTOP => [
                            'width' => 1050,
                            'height' => 750,
							'defaultImage' => 'banner-big-default.png'
                        ],
                        applicationConstants::SCREEN_IPAD => [
                            'width' => 1050,
                            'height' => 750,
							'defaultImage' => 'banner-big-default.png'
                        ],
                        applicationConstants::SCREEN_MOBILE => [
                            'width' => 1050,
                            'height' => 750,
							'defaultImage' => 'banner-big-default.png'
                        ],
                    ],
                    Collections::BANNER_POSITION_RIGHT => [
                        applicationConstants::SCREEN_DESKTOP => [
                            'width' => 510,
                            'height' => 750,
							'defaultImage' => 'banner-small-default.png'
                        ],
                        applicationConstants::SCREEN_IPAD => [
                            'width' => 510,
                            'height' => 750,
							'defaultImage' => 'banner-small-default.png'
                        ],
                        applicationConstants::SCREEN_MOBILE => [
                            'width' => 510,
                            'height' => 750,
							'defaultImage' => 'banner-small-default.png'
                        ],
                    ]
                ],
                Collections::TYPE_BANNER_LAYOUT5 => [
                    applicationConstants::SCREEN_DESKTOP => [
                        'width' => 1050,
                        'height' => 700,
						'defaultImage' => 'banner-big-default.png'
                    ],
                    applicationConstants::SCREEN_IPAD => [
                        'width' => 1050,
                        'height' => 700,
						'defaultImage' => 'banner-big-default.png'
                    ],
                    applicationConstants::SCREEN_MOBILE => [
                        'width' => 1050,
                        'height' => 700,
						'defaultImage' => 'banner-big-default.png'
                    ],
                ],
                Collections::TYPE_BANNER_LAYOUT_DETAIL => [
                    applicationConstants::SCREEN_DESKTOP => [
                        'width' => 1920,
                        'height' => 366,
						'defaultImage' => 'banner-big-default.png'
                    ],
                    applicationConstants::SCREEN_IPAD => [
                        'width' => 1920,
                        'height' => 366,
						'defaultImage' => 'banner-big-default.png'
                    ],
                    applicationConstants::SCREEN_MOBILE => [
                        'width' => 1920,
                        'height' => 366,
						'defaultImage' => 'banner-big-default.png'
                    ],
                ],
                Collections::TYPE_CONTENT_BLOCK_WITH_ICON_LAYOUT3 => [
                    applicationConstants::SCREEN_DESKTOP => [
                        'width' => 620,
                        'height' => 465,
						'defaultImage' => 'banner-big-default.png'
                    ],
                    applicationConstants::SCREEN_IPAD => [
                        'width' => 620,
                        'height' => 465,
						'defaultImage' => 'banner-big-default.png'
                    ],
                    applicationConstants::SCREEN_MOBILE => [
                        'width' => 620,
                        'height' => 465,
						'defaultImage' => 'banner-big-default.png'
                    ],
                ],
                Collections::TYPE_PRODUCT_LAYOUT2 => [
                    applicationConstants::SCREEN_DESKTOP => [
                        'width' => 259,
                        'height' => 147,
						'defaultImage' => 'banner-big-default.png'
                    ],
                    applicationConstants::SCREEN_IPAD => [
                        'width' => 259,
                        'height' => 147,
						'defaultImage' => 'banner-big-default.png'
                    ],
                    applicationConstants::SCREEN_MOBILE => [
                        'width' => 259,
                        'height' => 147,
						'defaultImage' => 'banner-big-default.png'
                    ],
                ],
            ]
        ];
    }
	
	public static function productImageSizeArr()
    {
        return [
			static::THEME_DEFAULT => [
                'width' => 500,
                'height' => 500,
                'thumb_width' => 330,
                'thumb_height' => 440,
            ],
            static::THEME_FASHION => [
                'width' => 660,
                'height' => 880,
                'thumb_width' => 330,
                'thumb_height' => 440,
            ], 
			static::THEME_HEAVY_EQUIPMENT => [
                'width' => 592,
                'height' => 444,
                'thumb_width' => 420,
                'thumb_height' => 315,
            ],
			static::THEME_AUTOMOBILE => [
                'width' => 740,
                'height' => 555,  
                'thumb_width' => 420,
                'thumb_height' => 315,
            ]
        ];
    }
    
    public static function blogImageSizeArr()
    {
        return [
			static::THEME_DEFAULT => [
                'width' => 500,
                'height' => 500,
            ],
            static::THEME_FASHION => [
                'width' => 660,
                'height' => 880,
            ],
			static::THEME_HEAVY_EQUIPMENT => [
                'width' => 656,
                'height' => 369,
            ],
			static::THEME_AUTOMOBILE => [
                'width' => 660,
                'height' => 495,
            ]
        ];
    }
    
    public static function brandFeaturedImageSizeArr()
    {
        return [
			static::THEME_DEFAULT => [
                'width' => 500,
                'height' => 500,
            ],
            static::THEME_FASHION => [
                'width' => 660,
                'height' => 880,
            ],
			static::THEME_HEAVY_EQUIPMENT => [
                'width' => 645,
                'height' => 377,
            ],
			static::THEME_AUTOMOBILE => [
                'width' => 120,
                'height' => 120,
            ]
        ];
    }
	
	public static function gridViewImagesSizeArr()
    {
        return [
		static::THEME_DEFAULT => [
			1 => [
                'width' => 682,
                'height' => 470,
            ],
            2 => [
                'width' => 516,
                'height' => 470,
            ],
            3 => [
                'width' => 682,
                'height' => 960,
            ],
            4 => [
                'width' => 516,
                'height' => 470,
            ],
            5 => [
                'width' => 784,
                'height' => 470,
			]
		],
		
		static::THEME_FASHION => [
			1 => [
                'width' => 564,
                'height' => 389,
            ],
            2 => [
                'width' => 366,
                'height' => 389,
            ],
            3 => [
                'width' => 564,
                'height' => 808,
            ],
            4 => [
                'width' => 366,
                'height' => 389,
            ],
            5 => [
                'width' => 564,
                'height' => 389,
			]
		],
		
		static::THEME_HEAVY_EQUIPMENT => [
			1 => [
                'width' => 375,
                'height' => 315,
            ],
            2 => [
                'width' => 375,
                'height' => 315,
            ],
            3 => [
                'width' => 375,
                'height' => 315,
            ],
            4 => [
                'width' => 375,
                'height' => 315,
            ],
            5 => [
                'width' => 780,
                'height' => 660,
			]
		],
		static::THEME_AUTOMOBILE => [
			1 => [
                'width' => 420,
                'height' => 315,
            ],
            2 => [
                'width' => 420,
                'height' => 315,
            ],
            3 => [
                'width' => 420,
                'height' => 315,
            ],
            4 => [
                'width' => 420,
                'height' => 315,
            ],
            5 => [
                'width' => 420,
                'height' => 315,
			],
			6 => [
				'width' => 420,
                'height' => 315,
			]
		],
		
		];
    }
	
	public static function heroSlideImageSizeArr()
    {
        return [
			static::THEME_DEFAULT => [
                'width' => 2000,
                'height' => 600,
            ],
            static::THEME_FASHION => [
                'width' => 2000,
                'height' => 1000,
            ],
			static::THEME_HEAVY_EQUIPMENT => [
                'width' => 1920,
                'height' => 700,
            ],
			static::THEME_AUTOMOBILE => [
                'width' => 1920,
                'height' => 700,
            ]
        ];
    }
    
    public static function productCategoryIconSizeArr()
    {
        return [
			static::THEME_DEFAULT => [
                'width' => 60,
                'height' => 60,
            ],
            static::THEME_FASHION => [
                'width' => 32,
                'height' => 32,
            ], 
			static::THEME_HEAVY_EQUIPMENT => [
                'width' => 380,
                'height' => 285,
            ],
			static::THEME_AUTOMOBILE => [
                'width' => 380,
                'height' => 285,
            ]
        ];
    }
    
    public static function productCategoryBannerSizeArr()
    {
        return [
			static::THEME_DEFAULT => [
                'width' => 2000,
                'height' => 500,
            ],
            static::THEME_FASHION => [
                'width' => 2000,
                'height' => 500,
            ], 
			static::THEME_HEAVY_EQUIPMENT => [
                'width' => 2000,
                'height' => 500,
            ],
			static::THEME_AUTOMOBILE => [
                'width' => 2000,
                'height' => 500,
            ]
        ];
    }
    
	
	
	

}
