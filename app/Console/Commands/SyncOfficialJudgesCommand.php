<?php

namespace App\Console\Commands;

use App\Http\Controllers\Admin\JudgeController;
use App\Models\Judge;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

#[Signature('app:sync-official-judges')]
#[Description('Sync all official festival judges and ensure accounts, secure PINs, and passwords exist')]
class SyncOfficialJudgesCommand extends Command
{
    /**
     * Official stage and category judges roster for QUAF Season 09.
     */
    protected array $officialJudges = [
        [
            'name' => 'Rafi Ahsani',
            'specialization' => 'Elocution Malayalam, Wa’az, Fiqh Colloquium, Speech Malayalam (C Zone), Ideal Talk, Thadrees',
        ],
        [
            'name' => 'Shahabas Master',
            'specialization' => 'Elocution Malayalam, Wa’az, Speech Malayalam (C Zone)',
        ],
        [
            'name' => 'TP Cheruvadi',
            'specialization' => 'Mapilappattu, Qawwali, Maala Avishkaram, Qaseeda, Hamd Urdu, Nasheeda, Madh Song, Group Song, Na\'ath',
        ],
        [
            'name' => 'Noufal Master',
            'specialization' => 'Mapilappattu, Qawwali, Qaseeda, Hamd Urdu',
        ],
        [
            'name' => 'Abdurahman Hikami',
            'specialization' => 'Mapilappattu, Qawwali, Maala Avishkaram, Qaseeda, Hamd Urdu, Nasheeda, Madh Song, Group Song',
        ],
        [
            'name' => 'Qadar Saqafi',
            'specialization' => 'Qira\'ath sessions across stages',
        ],
        [
            'name' => 'Shanid Saqafi',
            'specialization' => 'Qira\'ath sessions across stages',
        ],
        [
            'name' => 'Anwar Latheefi',
            'specialization' => 'Qira\'ath sessions across stages',
        ],
        [
            'name' => 'Yoonus Saqafi',
            'specialization' => 'Wa’az, Fiqh Colloquium, Maala Avishkaram, Ideal Talk, Thadrees',
        ],
        [
            'name' => 'Shaheed Nurani',
            'specialization' => 'Sulook, Arabic Speech',
        ],
        [
            'name' => 'Muzammil Nurani',
            'specialization' => 'Sulook, Arabic Speech',
        ],
        [
            'name' => 'Sinan Nurani',
            'specialization' => 'Sulook, Arabic Speech',
        ],
        [
            'name' => 'Irfan Suraiji Koyilandi',
            'specialization' => 'Fiqh Colloquium, Ideal Talk, Thadrees',
        ],
        [
            'name' => 'Shareef Saqafi',
            'specialization' => 'Nasheeda, Madh Song, Group Song, Na\'ath',
        ],
        [
            'name' => 'Adv. Zain Saqafi Mukkam',
            'specialization' => 'Speech English, Quaf x Talk, Bilingual Speech',
        ],
        [
            'name' => 'Sanahul Musthafa Saqafi',
            'specialization' => 'Speech English, Quaf x Talk, Bilingual Speech',
        ],
        [
            'name' => 'Adv. Saifudheen Saqafi',
            'specialization' => 'Speech English, Quaf x Talk, Bilingual Speech',
        ],
        [
            'name' => 'CK Muhammed Iringannur',
            'specialization' => 'Rihla, Speech Malayalam (A Zone), Social Debate, Q Talk, Book Discussion, Speech Urdu',
        ],
        [
            'name' => 'Shakeer Master',
            'specialization' => 'Rihla, Speech Malayalam (A Zone), Social Debate, Q Talk, Book Discussion, Speech Urdu',
        ],
        [
            'name' => 'MT Shihabudheen Saqafi',
            'specialization' => 'Rihla, Speech Malayalam (A Zone), Social Debate, Q Talk, Book Discussion, Speech Urdu',
        ],
        [
            'name' => 'Hafiz Abdulla Suraiji',
            'specialization' => 'Q Talk',
        ],
        [
            'name' => 'Hafiz Nasrudheen Adany',
            'specialization' => 'Q Talk',
        ],
        [
            'name' => 'Mubashir Nurani',
            'specialization' => 'Extempore Malayalam',
        ],
        [
            'name' => 'Majid Saqafi Thaachanna',
            'specialization' => 'Extempore Malayalam',
        ],
        [
            'name' => 'Ustd. Umarali Saqafi',
            'specialization' => 'Sadai Nasam, Speech Urdu, Na\'ath',
        ],
        [
            'name' => 'Ustd. Saleem Saqafi',
            'specialization' => 'Sadai Nasam, Speech Urdu, Na\'ath',
        ],
        [
            'name' => 'Umair Bukhari Cherumuttam',
            'specialization' => 'Speech Urdu, Rihla Discussion',
        ],
    ];

    public function handle(): int
    {
        $this->info('Starting sync of official festival judges roster...');

        $createdCount = 0;
        $existingCount = 0;
        $reportRows = [];

        DB::transaction(function () use (&$createdCount, &$existingCount, &$reportRows) {
            foreach ($this->officialJudges as $item) {
                $name = trim($item['name']);
                $spec = $item['specialization'];

                // Case-insensitive lookup to avoid duplicates
                $judge = Judge::whereRaw('LOWER(TRIM(name)) = ?', [strtolower($name)])->first();

                if ($judge) {
                    $existingCount++;
                    $user = $judge->user;

                    // Ensure Judge has a connected user account
                    if (! $user && $judge->user_id) {
                        $user = User::find($judge->user_id);
                    }

                    if (! $user) {
                        $email = Str::slug($name).rand(100, 999).'@quaf.fest';
                        $plainPassword = 'Judge@'.rand(1000, 9999);
                        $user = User::create([
                            'name' => $name,
                            'email' => $email,
                            'password' => Hash::make($plainPassword),
                            'plain_password' => $plainPassword,
                            'role' => 'judge',
                            'phone' => $judge->contact,
                            'is_active' => true,
                        ]);
                        $judge->update(['user_id' => $user->id]);
                    }

                    // Update specialization if not set
                    if (empty($judge->specialization)) {
                        $judge->update(['specialization' => $spec]);
                    }

                    $reportRows[] = [
                        $judge->id,
                        $judge->name,
                        $judge->access_code,
                        $user?->email ?? 'N/A',
                        $user?->plain_password ?? '***',
                        'Existing',
                    ];
                } else {
                    $createdCount++;
                    $email = Str::slug($name).rand(100, 999).'@quaf.fest';
                    $plainPassword = 'Judge@'.rand(1000, 9999);
                    $accessCode = JudgeController::generateToughPin(4);

                    $user = User::create([
                        'name' => $name,
                        'email' => $email,
                        'password' => Hash::make($plainPassword),
                        'plain_password' => $plainPassword,
                        'role' => 'judge',
                        'is_active' => true,
                    ]);

                    $judge = Judge::create([
                        'user_id' => $user->id,
                        'name' => $name,
                        'access_code' => $accessCode,
                        'designation' => 'Official Judge',
                        'specialization' => $spec,
                        'notes' => 'Official festival jury panel',
                    ]);

                    $reportRows[] = [
                        $judge->id,
                        $judge->name,
                        $judge->access_code,
                        $user->email,
                        $plainPassword,
                        'Created',
                    ];
                }
            }
        });

        $this->table(
            ['ID', 'Judge Name', 'Access PIN', 'Login Email', 'Password', 'Status'],
            $reportRows
        );

        $this->info("Successfully synchronized judges. Created: {$createdCount}, Existing: {$existingCount}.");
        $this->comment('Programs were left unassigned as requested (ready for manual assignment in Admin Panel).');

        return Command::SUCCESS;
    }
}
