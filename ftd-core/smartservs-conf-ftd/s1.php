<?php
$AppConfig = array (
        'db'                                   => array (
                'host'                         => 'localhost',
                'user'                         => '',
                'password'                     => '',
                'database'                     => '',
                   ),
		            'paylink' => array (
                  	'apiId' => '',
                  	'secretKey' => ''
                ),
                'Game'                         => array (
                // اعدادات اللعبه
                'speed'                        => '10000',//سرعة اللعبه
                'speed_t'                      => '500',//سرعه توقيت تدريب القوات
                'speed_b'                      => '200',//سرعه توقيت بناء المباني
                'dev_troop_to_20'              => '1000',//ذهب افران صهر الحديد بناء الى 20
                'WWW'                          => '100',//مضروب مووارد بناء المعجزه
                'WWW2'                         => '25',// مضروب توقيت بناء المعجزه
                'map'                          => '401',//حجم الخريطه
						'tid_npc'                => '100',  ///مبادلة القوات بالسوق							
                'attack'                       => '10000',//100سرعه الهجوم يفضل
                'protection'                   => '1',// الحمايه بالساعات
                'protectionx'                  => '0',//تدبيل الحمايه
                'X'                            => '1328827643',//غير مهم يرجى تركه هكذا
                'capacity'                     => '10000', // المخازن يفضل 125
                'cranny'                       => '10', // المخابأ
                'cp'                           => '100',//ولاء القريه الجديده
				'auto_training_t'              => '120', // زمن التدريب التلقائي
				'auto_training_g'              => '120',
                'market'                       => '30000', // حمولة التجار
                'plusoases'                    => '1000',// ذهب شراء الواحات
                'plusoases_count'              => '5',// اقصى عدد من الواحات يمكن شراؤه
                'backtroops'        	       => '1000',		 // ارجاع التعزيزات
                'speedmarket'                  => '500', // سرعه التجار
		'hero_gold'                    => '5000', // انهاء البطل فورا
                //البلاس
                'plus1'                        => '1',//مده بلاس بالايام
                'plus2'                        => '1',//مده الزياده بالايام
                'plus3'                        => '1',//قائمة بلاس
                'plus4'                        => '1',// زياده بلاس
                'plus5'                        => '2',//انهاء المباني فورأ
                'plus6'                        => '3',//تاجر مبادله
                'plus7'                        => '35',//انهاء تدريب القوات فورأ
                'plus9'                        => '1000',//انهاء التعزيزات فورأ
                'plus_by_abdullah_1_1'         => '5',
                'plus_by_abdullah_1_2'         => '100',
                'plus_by_abdullah_2_1'         => '5',
                'plus_by_abdullah_2_2'         => '100',
                'plus_by_abdullah_3_1'         => '5',
                'plus_by_abdullah_3_2'         => '100',
                'plus_by_abdullah_4_1'         => '5',
                'plus_by_abdullah_4_2'         => '100',
                'plus_by_abdullah_5_1'         => '5',
                'plus_by_abdullah_5_2'         => '1000000',
                'plus_by_abdullah_6_1'         => '5',
                'plus_by_abdullah_6_2'         => '100',
               
                'berq_time'     => '1200', //وقت البيرق
                'berq_gold'     => '3500000', //ذهب البيرق
               
				//مميزات خاصة //
                'activitebuytroops'            => '0',// 1 سوق المحاربين شغال 0 تعطيل
                'plus7_on'                     => '1',//تفعيل خصائص نورسين
                'activitebuyres'               => '0',// 1 شراء الموارد شغال 0 تعطيل
                'res'                          => '250000',// 1 شراء الموارد شغال 0 تعطيل
				'mkale'                        => '10', // المقاليع 10 للتشغيل 21 للايقاف
				'link'                         => '1',// ربح الذهب  1 يعمل 0 موقوف
				'silver'                       => '0',// الفضة  1 يعمل 0 موقوف
				'animal'                       => '1',// الهدية اليومية  1 يعمل 0 موقوف
				'hdya'                         => '1',// الهدية اليومية  1 يعمل 0 موقوف
				'copon'                        => '1',// كوبون الذهب 1 يعمل 0 موقوف
                //سوق المحاربين
                'piyadeh'                      => '0.010000',// الجندي في سوق المحاربي 0.0002
                'piyadeh1'                     => '0.010000',// الجندي رقم 2 في سوق المحاربي
                'piyadeh2'                     => '0.010000',// الجندي رقم 3 في سوق المحاربي
                'ksaf'                         => '0.010000',// الكشاف في سوق المحاربي
                'savareh'                      => '0.030000', // الفرسان في سوق المحاربين
                'shovalieh'                    => '0.0600',//المقلاع في سوق المحاربين

                //امـور عامه
                'pepolegold'                   => '1500',//سكان استلام الذهب لكسب الذهب
                'gift_time'  			=> '59', 			// الوقت
                'gift_gold'  			=> '10000', 			// عدد الذهب
		'gift_silver'       	=> '20000', 			//عدد الفضة
                'setgold'                      => '10000',//الذهب المعطى لكسب الذهب
                'bonous'                       => '200',//غير مهم
                'osas'                         => '5000',// الواحات العاديه
		'num_diamond'                  => '0', // num diamond
		'hero_gold'                    =>  3000, // سعر انهاء البطل
                'osasX'                        => '5000',// الواحات الكبيره القمح
                'online'                       => '222422',//المتصلين خروجهم بعد
                'freegold'                     => '10000',//ذهب مجاني عند التسجيل
                'Warrior'                     => '0',//ذهب مجاني عند التسجيل

                'freegoldweek'                 => '10000',//ذهب مجاني أسبوعي
                'awsmh'                        => '432000',// مده توزيع الاوسمه بالثواني 
                'RegisterOver'                 => '15',//مدة اغلاق التسجيل عدد الايام
                'farmtime'                     => '5',// مدة الانتظار لارسال هجمة المزرعة من جديد
        ),
        'page'                                 => array (
                'ar_title'                     => 'حرب التتار - حرب التتار حرب التتار السريع',
                'asset_version'                => 'Nhjkh1ka111alcMfks'
        ),
        'system'                               => array (
                'blocklistword'                => array( "goo.gl", "tatar4", "tatar6", "acunetix", "SomeCustomInjectedHeader", "0x31", "UNION", "' or", "NdcMasterLog", "installgame", "InstallGame", "iron-sy", "elaml.org", "lionab"),
                'adminName'                    => 'admin', //admin
                'adminPassword'                => '4297f44b13955235245b2497399d7a93',//باس الادمن

                'tatar'                        => '2',//مده نزول التتار بالايام
                'artef'                        => '1',//مده نزول التحف بالايام
                'start'                        => '',//مده العداد بالايام
                'endin'                        => '1',//مده اعاده سيرفر حرب التتار بالساعات
				'server_start'           => '2021/05/10 16:00:00',//وقت فتح السيرفر بالتاريخ ليكون فى رئيسية الموقع

                'lang'                         => 'ar',//اللغه
                'admin_email'                  => 'mtaaa10@gmail.com
',//الايميل
                'webname'                      => 'tatarsit - حرب التتار',
                'email'                        => 'mtaaa10@gmail.com
',//المرسل
                'namesite'                     => 'Tatar War Falcon',//اسم الموقع انجليزي
                'linksite'                     => 'https://ltatar.com/ftd-style/',//رابط الصور
                'installkey'                   => 'inst',//رمز التسطيب

),

       'plus'                                  => array (
                'packages'                     => array (
                        /*array (
                                'name'         => 'sms',
                                'gold'         => 2000,
                                'cost'         => 10,
                                'plus'         => 0,
                                'currency'     => 'ريال',
                                'image'        => 'sms.png'
                        ),*/

			array ( 
				'name'		=> 'الحزمة 1',
				'gold'		=> 10000,
				'goldplus'	=> 10000,
				'plus'		=> 20,
				'cost'		=> 00.10,
				'costplus'	=> 00.10,
				'currency'	=> 'usd',
				'image'		=> 'package_a.jpg'
			),
			array ( 
				'name'		=> 'الحزمة 2',
				'gold'		=> 20000,
				'goldplus'	=> 20000,
				'plus'		=> 30,
				'cost'		=> 2.00,
				'costplus'	=> 2.00,
				'currency'	=> 'usd',
				'image'		=> 'package_b.jpg'
			),
			array ( 
				'name'		=> 'الحزمة 3',
				'gold'		=> 457000,
				'goldplus'	=> 600000,
				'plus'		=> 40,
				'cost'		=> 106.65,
				'costplus'	=> 106.65,
				'currency'	=> 'usd',
				'image'		=> 'package_c.jpg'
			),
			array ( 
				'name'		=> 'الحزمة 4',
				'gold'		=> 800000,
				'goldplus'	=> 1200000,
				'plus'		=> 50,
				'cost'		=> 213.29,
				'costplus'	=> 213.29,
				'currency'	=> 'usd',
				'image'		=> 'package_c.jpg'
			),
			array ( 
				'name'		=> 'حزمة 5',
				'gold'		=> 2200000,
				'goldplus'	=> 3000000,
				'plus'		=> 80,
				'cost'		=> 533.3,
				'costplus'	=> 533.3,
				'currency'	=> 'usd',
				'image'		=> 'package_c.jpg'
            ),
            			array ( 
				'name'		=> 'حزمة 6',
				'gold'		=> 4700000,
				'goldplus'	=> 6000000,
				'plus'		=> 130,
				'cost'		=> 1066.5,
				'costplus'	=> 1066.5,
				'currency'	=> 'usd',
				'image'		=> 'package_c.jpg'
            ),

               ),

                'payments'                     => array (

                        'paypal'               => array (

                                'testMode'     => false,

                                'name'         => 'PayPal',
 
                                'image'        => 'PayPal-logo-1.png',

				'merchant_id'	=> 'Khalidskh@hotmail.com',

				'currency'		=> 'USD'

                        ),
                        'paylink'	=> array (
				'testMode'		=> false,
				'name'			=> 'Visa | Mastercard | Mada',
				'image'			=> 'paylink.jpg',
				'merchant_id'	=> '',
				'currency'		=> 'USD'
				),
                        'apple_pay'	=> array (
				'testMode'		=> false,
				'name'			=> 'Apple Pay',
				'image'			=> 'apple.jpg',
				'merchant_id'	=> '',
				'currency'		=> 'USD'
				)



                )

        )



);

?>