<?php

namespace App\Console\Commands;

use App\Models\Student;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

#[Signature('app:sync-student-accounts')]
#[Description('Pre-generate User accounts for all registered Students to prevent CPU spikes and concurrency issues during peak login')]
class SyncStudentAccountsCommand extends Command
{
    public function handle(): int
    {
        $this->info('Checking registered students without user accounts...');

        $students = Student::whereNull('user_id')->get();
        $total = $students->count();

        if ($total === 0) {
            $this->info('All registered students already have linked user accounts.');

            return Command::SUCCESS;
        }

        $this->info("Found {$total} students without user accounts. Generating accounts...");

        // Pre-hash once to avoid 1000+ expensive Bcrypt calculations
        $sharedPasswordHash = Hash::make(Str::random(32));

        $count = 0;
        DB::transaction(function () use ($students, $sharedPasswordHash, &$count) {
            foreach ($students as $student) {
                $email = Str::slug($student->student_id).'@student.quaf.org';

                $user = User::firstOrCreate(
                    ['email' => $email],
                    [
                        'name' => $student->name,
                        'password' => $sharedPasswordHash,
                        'role' => 'student',
                        'phone' => $student->contact,
                        'is_active' => true,
                    ]
                );

                $student->update(['user_id' => $user->id]);
                $count++;
            }
        });

        $this->info("Successfully linked user accounts for {$count} students.");

        return Command::SUCCESS;
    }
}
