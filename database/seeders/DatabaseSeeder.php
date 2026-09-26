<?php

namespace Database\Seeders;

use App\Models\FestivalSetting;
use App\Models\Group;
use App\Models\Judge;
use App\Models\PointSetting;
use App\Models\Program;
use App\Models\ProgramCategory;
use App\Models\Stage;
use App\Models\Student;
use App\Models\User;
use App\Services\PointCalculationService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Festival Settings
        FestivalSetting::set('live_fest_mode', '1');
        FestivalSetting::set('festival_name', 'QUAF — Season 09');
        FestivalSetting::set('festival_dates', 'October 24 - 28, 2026');
        FestivalSetting::set('tagline', 'Adabic Inheritance — Samastha Centenary');
        FestivalSetting::set('organizer', 'Ihyaussunna Students Union, Markazu Saquafathi Sunniyya');
        FestivalSetting::set('registration_open', '1');

        // 2. Point Settings
        $pointSetting = PointSetting::firstOrCreate([], [
            'first_place_points' => 10,
            'second_place_points' => 7,
            'third_place_points' => 5,
            'participation_points' => 1,
            'group_multiplier' => 2.00,
        ]);

        // 3. Create Users
        $superAdmin = User::updateOrCreate(['email' => 'admin@quaf.fest'], [
            'name' => 'QUAF Central Admin',
            'password' => Hash::make('password'),
            'role' => 'super_admin',
            'phone' => '+91 98470 00001',
            'is_active' => true,
        ]);

        $greenRoomUser = User::updateOrCreate(['email' => 'greenroom@quaf.fest'], [
            'name' => 'Green Room Officer',
            'password' => Hash::make('password'),
            'role' => 'green_room_coordinator',
            'phone' => '+91 98470 00002',
            'is_active' => true,
        ]);

        $judgeUser1 = User::updateOrCreate(['email' => 'judge1@quaf.fest'], [
            'name' => 'Dr. Anas Al-Azhari',
            'password' => Hash::make('password'),
            'role' => 'judge',
            'phone' => '+91 98470 00003',
            'is_active' => true,
        ]);

        $judgeUser2 = User::updateOrCreate(['email' => 'judge2@quaf.fest'], [
            'name' => 'Prof. Zaid Rahman',
            'password' => Hash::make('password'),
            'role' => 'judge',
            'phone' => '+91 98470 00004',
            'is_active' => true,
        ]);

        $programCommitteeUser = User::updateOrCreate(['email' => 'samithi@quaf.fest'], [
            'name' => 'Program Samithi (പ്രോഗ്രാം സമിതി)',
            'password' => Hash::make('Samithi#2026@QuafFest!'),
            'role' => 'program_committee',
            'phone' => '+91 98470 00011',
            'is_active' => true,
        ]);

        $leaderUser1 = User::updateOrCreate(['email' => 'leader.lumo@quaf.fest'], [
            'name' => 'WARIS ADANY (Leader - LUMO FIKRIC)',
            'password' => Hash::make('Lumo#9482@FikricFest!26'),
            'role' => 'group_leader',
            'phone' => '+91 98470 00005',
            'is_active' => true,
        ]);

        $leaderUser2 = User::updateOrCreate(['email' => 'leader.pacto@quaf.fest'], [
            'name' => 'BASIL ADANY (Leader - PACTO HIKMIC)',
            'password' => Hash::make('Pacto$Hikmic*8319#Q9'),
            'role' => 'group_leader',
            'phone' => '+91 98470 00006',
            'is_active' => true,
        ]);

        $leaderUser3 = User::updateOrCreate(['email' => 'leader.conco@quaf.fest'], [
            'name' => 'SINAN SAQAFI VELLIMUTTAM (Leader - CONCO MAJDIC)',
            'password' => Hash::make('Majdic&Conco%6724!Apex'),
            'role' => 'group_leader',
            'phone' => '+91 98470 00008',
            'is_active' => true,
        ]);

        $leaderUser4 = User::updateOrCreate(['email' => 'leader.unio@quaf.fest'], [
            'name' => 'ANAS ADANY (Leader - UNIO HILMIC)',
            'password' => Hash::make('Unio_5193-Hilmic@9Fest'),
            'role' => 'group_leader',
            'phone' => '+91 98470 00009',
            'is_active' => true,
        ]);

        $leaderUser5 = User::updateOrCreate(['email' => 'leader.yugo@quaf.fest'], [
            'name' => 'JABIR SAQAFI (Leader - YUGO RUSHDIC)',
            'password' => Hash::make('Yugo!Rushdic?3825#Shield'),
            'role' => 'group_leader',
            'phone' => '+91 98470 00010',
            'is_active' => true,
        ]);

        $studentUser1 = User::updateOrCreate(['email' => 'student@quaf.fest'], [
            'name' => 'JAMALUDHEEN ABDUL HAMEED',
            'password' => Hash::make('password'),
            'role' => 'student',
            'phone' => '+91 98470 00007',
            'is_active' => true,
        ]);

        // 4. Groups (5 Houses from Karadu Rekha)
        $groupData = [
            [
                'name' => 'Lumo Fikric',
                'code' => 'LUMO',
                'slug' => 'lumo-fikric',
                'color_hex' => '#56286b',
                'leader_id' => $leaderUser1->id,
                'manager_name' => 'WARIS ADANY',
                'assistant_managers' => ['SUFIYAN KOOTTAMPARA', 'JAFAR GUDALUR'],
                'name_in_results' => 'Lumo Fikric',
                'name_in_certificates' => 'Lumo Fikric',
                'admin_password' => 'Lumo#9482@FikricFest!26',
                'logo_url' => null,
            ],
            [
                'name' => 'Pacto Hikmic',
                'code' => 'PACTO',
                'slug' => 'pacto-hikmic',
                'color_hex' => '#2e3192',
                'leader_id' => $leaderUser2->id,
                'manager_name' => 'BASIL ADANY',
                'assistant_managers' => ['ASHIQ CHENGANASSERI'],
                'name_in_results' => 'Pacto Hikmic',
                'name_in_certificates' => 'Pacto Hikmic',
                'admin_password' => 'Pacto$Hikmic*8319#Q9',
                'logo_url' => null,
            ],
            [
                'name' => 'Conco Majdic',
                'code' => 'CONCO',
                'slug' => 'conco-majdic',
                'color_hex' => '#f8e709',
                'leader_id' => $leaderUser3->id,
                'manager_name' => 'SINAN SAQAFI VELLIMUTTAM',
                'assistant_managers' => ['MUSHARAF PONNANI', 'ZAINUL ABID VAVAD'],
                'name_in_results' => 'Conco Majdic',
                'name_in_certificates' => 'Conco Majdic',
                'admin_password' => 'Majdic&Conco%6724!Apex',
                'logo_url' => null,
            ],
            [
                'name' => 'Unio Hilmic',
                'code' => 'UNIO',
                'slug' => 'unio-hilmic',
                'color_hex' => '#7f1518',
                'leader_id' => $leaderUser4->id,
                'manager_name' => 'ANAS ADANY',
                'assistant_managers' => ['HASEEB VENNIYUR'],
                'name_in_results' => 'Unio Hilmic',
                'name_in_certificates' => 'Unio Hilmic',
                'admin_password' => 'Unio_5193-Hilmic@9Fest',
                'logo_url' => null,
            ],
            [
                'name' => 'Yugo Rushdic',
                'code' => 'YUGO',
                'slug' => 'yugo-rushdic',
                'color_hex' => '#ad1e56',
                'leader_id' => $leaderUser5->id,
                'manager_name' => 'JABIR SAQAFI',
                'assistant_managers' => ['SALMAN PAKKANA'],
                'name_in_results' => 'Yugo Rushdic',
                'name_in_certificates' => 'Yugo Rushdic',
                'admin_password' => 'Yugo!Rushdic?3825#Shield',
                'logo_url' => null,
            ],
        ];

        $groups = [];
        foreach ($groupData as $gd) {
            $groups[$gd['code']] = Group::updateOrCreate(['code' => $gd['code']], $gd);
        }
        $groups['GROUP A'] = $groups['LUMO'];

        // 5. Program Categories
        $categoriesData = [
            ['name' => 'Elocution & Oratory', 'slug' => 'elocution', 'description' => 'Eloquent speech in Arabic, English, Urdu, and Malayalam', 'icon' => 'mic'],
            ['name' => 'Vocal & Choral Arts', 'slug' => 'vocal-arts', 'description' => 'Sufi melodies, Qawwali, Nasheed, and Group Choral', 'icon' => 'music'],
            ['name' => 'Calligraphy & Fine Arts', 'slug' => 'fine-arts', 'description' => 'Thuluth, Kufic calligraphy, pencil sketching, water coloring', 'icon' => 'pen-tool'],
            ['name' => 'Literature & Verses', 'slug' => 'literature', 'description' => 'Poetry recital, creative essay writing, story composition', 'icon' => 'book-open'],
            ['name' => 'Stage Dramatics', 'slug' => 'stage-arts', 'description' => 'Theatrical performance, mime, and socio-cultural drama', 'icon' => 'theater'],
        ];

        $categories = [];
        foreach ($categoriesData as $cd) {
            $categories[$cd['slug']] = ProgramCategory::updateOrCreate(['slug' => $cd['slug']], $cd);
        }

        // 6. Stages
        $stageData = [
            ['name' => 'Stage 01 — Grand Amphitheatre', 'code' => 'STG-01', 'location' => 'Main Campus Plaza', 'capacity' => 1200, 'status' => 'active'],
            ['name' => 'Stage 02 — Heritage Hall', 'code' => 'STG-02', 'location' => 'Academic Wing B', 'capacity' => 500, 'status' => 'active'],
            ['name' => 'Stage 03 — Chamber of Eloquence', 'code' => 'STG-03', 'location' => 'Auditorium East', 'capacity' => 350, 'status' => 'active'],
            ['name' => 'Stage 04 — Harmony Arena', 'code' => 'STG-04', 'location' => 'Cultural Pavilion', 'capacity' => 600, 'status' => 'break'],
        ];

        $stages = [];
        foreach ($stageData as $sd) {
            $stages[$sd['code']] = Stage::updateOrCreate(['code' => $sd['code']], $sd);
        }

        // 7. Programs (144 Official Programs synced via command)
        Artisan::call('app:sync-official-programs');

        $programs = Program::all()->keyBy('code');

        // 9. Judges
        $judge1 = Judge::updateOrCreate(['user_id' => $judgeUser1->id], [
            'name' => 'Dr. Anas Al-Azhari',
            'designation' => 'Professor of Arabic Rhetoric',
            'specialization' => 'Arabic Literature & Eloquence',
            'contact' => '+91 98470 00003',
            'bio' => 'Renowned scholar of Arabic eloquence and linguistics with 18 years of national festival judging experience.',
        ]);

        $judge2 = Judge::updateOrCreate(['user_id' => $judgeUser2->id], [
            'name' => 'Prof. Zaid Rahman',
            'designation' => 'Vocal Musicologist & Critic',
            'specialization' => 'Sufi Maqam & Vocal Art',
            'contact' => '+91 98470 00004',
            'bio' => 'Classical vocal trainer and specialist in Ottoman and Andalusian spiritual melodies.',
        ]);

        if (isset($programs['Q9-101'], $programs['Q9-103'], $programs['Q9-106'])) {
            $judge1->programs()->sync([$programs['Q9-101']->id, $programs['Q9-103']->id, $programs['Q9-106']->id]);
        }
        if (isset($programs['Q9-102'], $programs['Q9-104'], $programs['Q9-105'])) {
            $judge2->programs()->sync([$programs['Q9-102']->id, $programs['Q9-104']->id, $programs['Q9-105']->id]);
        }

        // 11. Students (30 Official Students of GROUP A)
        $students = [];
        $officialGroupAStudents = [
            ['chest_no' => 'QF1001', 'name' => 'JAMALUDHEEN ABDUL HAMEED', 'class' => 'TQS', 'zone' => 'A ZONE'],
            ['chest_no' => 'QF1002', 'name' => 'RAZI MANJANADI', 'class' => 'TQS', 'zone' => 'A ZONE'],
            ['chest_no' => 'QF1003', 'name' => 'TWAYYIB JARAMKANDI', 'class' => 'TQS', 'zone' => 'A ZONE'],
            ['chest_no' => 'QF1004', 'name' => 'SALMANUL FARIS MAMBURAM', 'class' => 'TQS', 'zone' => 'A ZONE'],
            ['chest_no' => 'QF1005', 'name' => 'JABIR PAKARA', 'class' => 'TQS', 'zone' => 'A ZONE'],
            ['chest_no' => 'QF1006', 'name' => 'LIYAKATH TV', 'class' => 'S4', 'zone' => 'A ZONE'],
            ['chest_no' => 'QF1007', 'name' => 'SHEHIN KT', 'class' => 'S4', 'zone' => 'A ZONE'],
            ['chest_no' => 'QF1008', 'name' => 'SHUAIB', 'class' => 'S4', 'zone' => 'A ZONE'],
            ['chest_no' => 'QF1009', 'name' => 'FAKHRUDDIN THASHFEEQ', 'class' => 'S4', 'zone' => 'A ZONE'],
            ['chest_no' => 'QF1010', 'name' => 'ALI', 'class' => 'S4', 'zone' => 'A ZONE'],
            ['chest_no' => 'QF1011', 'name' => 'MUHAMMED UNAIS.P.M', 'class' => 'S4', 'zone' => 'A ZONE'],
            ['chest_no' => 'QF1012', 'name' => 'MUHAMMED SALMAN KP', 'class' => 'S4', 'zone' => 'A ZONE'],
            ['chest_no' => 'QF1013', 'name' => 'MUHAMMED UVAIS P', 'class' => 'S4', 'zone' => 'A ZONE'],
            ['chest_no' => 'QF1014', 'name' => 'SHAQEEB IHSAN C M', 'class' => 'S4', 'zone' => 'A ZONE'],
            ['chest_no' => 'QF1015', 'name' => 'MOHAMED BUJAIR NP', 'class' => 'S4', 'zone' => 'A ZONE'],
            ['chest_no' => 'QF1016', 'name' => 'SHAKEEL MUBARAK CP', 'class' => 'S4', 'zone' => 'A ZONE'],
            ['chest_no' => 'QF1017', 'name' => 'ADHIL MS', 'class' => 'S4', 'zone' => 'A ZONE'],
            ['chest_no' => 'QF1018', 'name' => 'MUHAMMED SUHAIL', 'class' => 'S4', 'zone' => 'A ZONE'],
            ['chest_no' => 'QF1019', 'name' => 'MAHAMMAD SADIK K', 'class' => 'S4', 'zone' => 'A ZONE'],
            ['chest_no' => 'QF1020', 'name' => 'MUHAMMED RAFI', 'class' => 'S4', 'zone' => 'A ZONE'],
            ['chest_no' => 'QF1021', 'name' => 'MOHAMMED RASIQ K', 'class' => 'S4', 'zone' => 'A ZONE'],
            ['chest_no' => 'QF1022', 'name' => 'MUHAMMED MUDHASIR KT', 'class' => 'S4', 'zone' => 'A ZONE'],
            ['chest_no' => 'QF1023', 'name' => 'ZIYAD ZAINUDHEEN', 'class' => 'UT4', 'zone' => 'A ZONE'],
            ['chest_no' => 'QF1024', 'name' => 'SINAN KT', 'class' => 'UT4', 'zone' => 'A ZONE'],
            ['chest_no' => 'QF1025', 'name' => 'SINAN KARUVANKALLU', 'class' => 'UT4', 'zone' => 'A ZONE'],
            ['chest_no' => 'QF1026', 'name' => 'SHIBILI', 'class' => 'UT4', 'zone' => 'A ZONE'],
            ['chest_no' => 'QF1027', 'name' => 'JAZIL RAHMAN', 'class' => 'UT4', 'zone' => 'A ZONE'],
            ['chest_no' => 'QF1028', 'name' => 'SINAN MK', 'class' => 'UT4', 'zone' => 'A ZONE'],
            ['chest_no' => 'QF1029', 'name' => 'MUHAMMIL', 'class' => 'UT4', 'zone' => 'A ZONE'],
            ['chest_no' => 'QF1030', 'name' => 'AKHIL', 'class' => 'UT4', 'zone' => 'A ZONE'],
        ];

        foreach ($officialGroupAStudents as $index => $item) {
            $students[] = Student::updateOrCreate(
                ['student_id' => $item['chest_no']],
                [
                    'user_id' => ($index === 0) ? $studentUser1->id : null,
                    'group_id' => $groups['GROUP A']->id,
                    'name' => $item['name'],
                    'category' => 'A Zone',
                    'class_level' => $item['class'],
                    'gender' => 'Male',
                    'contact' => '+91 98470 '.str_pad((string) (10000 + $index), 5, '0', STR_PAD_LEFT),
                    'photo_url' => null,
                    'qr_token' => Str::random(40),
                ]
            );
        }

        // 12. Seed Official Conco Majdic Students
        $this->call(ConcoMajdicStudentsSeeder::class);

        // 13. Calculate Initial Points & Rankings
        app(PointCalculationService::class)->recalculateAllPoints();
    }
}
