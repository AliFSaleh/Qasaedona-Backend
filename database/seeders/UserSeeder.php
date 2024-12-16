<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        User::truncate();
        Schema::enableForeignKeyConstraints();

        $admin = User::create([
            'id'                => 1,
            'name'              => 'الأدمن الحاج اسماعيل',
            'email'             => 'admin@gmail.com',
            'password'          => Hash::make('password'),
            'has_account'       => true,
            'is_verified'       => true,
            'email_verified_at' => now(),
        ]);
        $admin->assignRole('admin');

        $user_1 = User::create([
            'id'                => 2,
            'name'              => 'علي صالح',
            'email'             => 'ali@gmail.com',
            'password'          => Hash::make('password'),
            'has_account'       => true,
            'is_verified'       => true,
            'email_verified_at' => now(),
        ]);
        $user_1->assignRole('user');

        $user_2 = User::create([
            'id'                => 3,
            'name'              => 'وجدي العيد',
            'email'             => 'wajdi@gmail.com',
            'password'          => Hash::make('password'),
            'has_account'       => true,
            'is_verified'       => true,
            'email_verified_at' => now(),
        ]);
        $user_2->assignRole('user');

        $user_3 = User::create([
            'id'                => 4,
            'name'              => 'محمد بركات',
            'email'             => 'mohamad@gmail.com',
            'password'          => Hash::make('password'),
            'has_account'       => true,
            'is_verified'       => true,
            'email_verified_at' => now(),
        ]);
        $user_3->assignRole('user');

        $user_4 = User::create([
            'id'                => 5,
            'name'              => 'نزار قباني',
            'email'             => 'nezar@gmail.com',
            'bio'               => 'نزار بن توفيق القباني (21 مارس 1923 – 30 أبريل 1998) شاعر سوري معاصر ودبلوماسي من أسرة عربية دمشقية عريقة. إذ يعتبر جده أبو خليل القباني من رواد المسرح العربي. درس الحقوق في الجامعة السورية وفور تخرجه منها عام 1945 انخرط في السلك الدبلوماسي متنقلًا بين عواصم مختلفة حتى قدّم استقالته عام 1966؛ أصدر أولى دواوينه عام 1944 بعنوان «قالت لي السمراء» وتابع عملية التأليف والنشر التي بلغت خلال نصف قرن 35 ديوانًا أبرزها «طفولة نهد» و«الرسم بالكلمات»، وقد أسس دار نشر لأعماله في بيروت باسم «منشورات نزار قباني» وكان لدمشق وبيروت حيِّزٌ خاصٌّ في أشعاره لعلَّ أبرزهما «القصيدة الدمشقية» و«يا ست الدنيا يا بيروت». أحدثت حرب 1967 والتي أسماها العرب «النكسة» مفترقًا حاسمًا في تجربته الشعرية والأدبية، إذ أخرجته من نمطه التقليدي بوصفه «شاعر الحب والمرأة» لتدخله معترك السياسة، وقد أثارت قصيدته «هوامش على دفتر النكسة» عاصفة في الوطن العربي وصلت إلى حد منع أشعاره في وسائل الإعلام.—قال عنه الشاعر الفلسطيني عز الدين المناصرة: (نزار كما عرفته في بيروت هو أكثر الشعراء تهذيبًا ولطفًا).',
            'password'          => Hash::make('password'),
            'phone_country_id'  => 2,
            'phone'             => 0566337511,
            'country_id'        => 212,
            'has_account'       => true,
            'is_verified'       => true,
            'email_verified_at' => now(),
        ]);
        $user_4->assignRole('poet');

        $user_5 = User::create([
            'id'                => 6,
            'name'              => 'عبد الستار الكاظمي',
            'email'             => null,
            'bio'               => 'الشيخ عبد الستار بن جليل بن كرم البديري الكاظمي، خطيب وشاعر، ولد في الكاظمية من أسرة أدبية، فهو الأخ الأكبر للشاعرين جابر وعادل الكاظمي، حصل على شهادة الماجستير في الأدب العربي، ومن أساتذته في الشعر والأدب الأستاذ صفاء الجلبي والشيخ الغراوي

هاجر إلى سوريا عام 1980، ومنها إلى قم عام 1982 ليدرس هناك في حوزتها الفقه والأصول والتفسير، ومن ثم قام بتدريس المنطق والبلاغة والصرف، ثم هاجر إلى الدنمارك ولا يزال يقيم فيها

له كتاب بعنوان (أم البنين والكرامة الإلهية ــ 2005) وديوان شعر بعنوان (حديث القافية)

قال في ترجمته السيد محمد علي الحلو: (أن أجيال المدرسة الأزرية لم تعد تقنع بما يلقيه التيار الأدبي "المنفتح" على ثقافات غيره والمُلغي لتراثياته العقائدية، والقانع بأدبيات حضارة هزيلة ووجدانيات سطحية محاذية، بل أعاد للقصيدة حيويتها وللغرض الشعري فاعليته في دراسة ذات الأمة وروائعها الحضارية المتجددة

هذا ما حصل للشيخ عبد الستار الكاظمي فقد قرأ روائع الأزري، واستلهم ولائيات جابر الكاظمي، ونزح إلى مدرسة السيد عيسى الكاظمي، وحاكى ملاحم الشيخ كاظم آل نوح والأستاذ الشيخ عبد الستار الكاظمي لم تكن روائعه الأدبية من تقليديات شعراء أسلافه، بل قرأ لذاته وأسس لذاته مدرسة ولائية كاظمية خاصة، على طراز بغدادي أنيق',
            'password'          => null,
            'phone_country_id'  => 2,
            'phone'             => 0566337522,
            'country_id'        => 212,
            'has_account'       => false,
            'is_verified'       => false,
            'email_verified_at' => null,
        ]);
        $user_5->assignRole('poet');
    }
}
