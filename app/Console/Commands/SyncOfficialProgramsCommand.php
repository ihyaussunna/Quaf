<?php

namespace App\Console\Commands;

use App\Models\Program;
use App\Models\ProgramCategory;
use App\Models\Stage;
use App\Models\Zone;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

#[Signature('app:sync-official-programs {--force : Force overwrite existing programs and limits}')]
#[Description('Sync the 144 official festival programs into database with zones, types, stages, and group participant limits.')]
class SyncOfficialProgramsCommand extends Command
{
    public function handle(): int
    {
        $this->info('Starting sync of 144 official QUAF Season 09 programs...');

        DB::transaction(function () {
            // 1. Ensure categories exist
            $categoriesData = [
                'elocution' => ['name' => 'Elocution & Oratory', 'slug' => 'elocution', 'description' => 'Eloquent speech, debates, talks, and public speaking', 'icon' => 'mic'],
                'vocal-arts' => ['name' => 'Vocal & Choral Arts', 'slug' => 'vocal-arts', 'description' => 'Qira\'ath, Sulook, devotional melodies, songs, and recitals', 'icon' => 'music'],
                'literature' => ['name' => 'Literature & Verses', 'slug' => 'literature', 'description' => 'Creative writing, story, essay, poem, and scholarly tests', 'icon' => 'book-open'],
                'fine-arts' => ['name' => 'Calligraphy & Fine Arts', 'slug' => 'fine-arts', 'description' => 'Photography, calligraphy, design, and visual arts', 'icon' => 'camera'],
                'stage-arts' => ['name' => 'Stage Dramatics', 'slug' => 'stage-arts', 'description' => 'Discussions, colloquiums, problem solving, and stage presentations', 'icon' => 'theater'],
            ];

            $categories = [];
            foreach ($categoriesData as $slug => $cd) {
                $categories[$slug] = ProgramCategory::firstOrCreate(['slug' => $slug], $cd);
            }

            // 2. Ensure Zones exist and fetch IDs
            $zones = [
                'A Zone' => Zone::where('code', 'A_ZONE')->value('id') ?? 1,
                'B Zone' => Zone::where('code', 'B_ZONE')->value('id') ?? 2,
                'C Zone' => Zone::where('code', 'C_ZONE')->value('id') ?? 3,
                'Mix Zone' => Zone::where('code', 'MIX_ZONE')->value('id') ?? 4,
            ];

            // 3. Fetch default stage for stage programs
            $mainStage = Stage::where('code', 'STG-01')->first() ?? Stage::first();

            // 4. Define all 144 official programs
            $programsList = [
                // A Zone (27 programmes: Q9-101 to Q9-127)
                ['code' => 'Q9-101', 'name' => "Qira'ath", 'type' => 'individual', 'is_stage' => true, 'zone' => 'A Zone', 'limit' => 2, 'cat' => 'vocal-arts'],
                ['code' => 'Q9-102', 'name' => 'Sulook', 'type' => 'individual', 'is_stage' => true, 'zone' => 'A Zone', 'limit' => 2, 'cat' => 'vocal-arts'],
                ['code' => 'Q9-103', 'name' => 'Malayalam Speech', 'type' => 'individual', 'is_stage' => true, 'zone' => 'A Zone', 'limit' => 2, 'cat' => 'elocution'],
                ['code' => 'Q9-104', 'name' => 'QUAF x Talk', 'type' => 'individual', 'is_stage' => true, 'zone' => 'A Zone', 'limit' => 2, 'cat' => 'elocution'],
                ['code' => 'Q9-105', 'name' => 'Urdu Speech', 'type' => 'individual', 'is_stage' => true, 'zone' => 'A Zone', 'limit' => 2, 'cat' => 'elocution'],
                ['code' => 'Q9-106', 'name' => 'Khutuba', 'type' => 'individual', 'is_stage' => true, 'zone' => 'A Zone', 'limit' => 2, 'cat' => 'elocution'],
                ['code' => 'Q9-107', 'name' => 'Problem Solving', 'type' => 'individual', 'is_stage' => true, 'zone' => 'A Zone', 'limit' => 2, 'cat' => 'stage-arts'],
                ['code' => 'Q9-108', 'name' => 'Tadrees', 'type' => 'individual', 'is_stage' => true, 'zone' => 'A Zone', 'limit' => 1, 'cat' => 'stage-arts'],
                ['code' => 'Q9-109', 'name' => 'Thasneef', 'type' => 'individual', 'is_stage' => false, 'zone' => 'A Zone', 'limit' => 2, 'cat' => 'literature'],
                ['code' => 'Q9-110', 'name' => 'Tatbeeq', 'type' => 'individual', 'is_stage' => false, 'zone' => 'A Zone', 'limit' => 2, 'cat' => 'literature'],
                ['code' => 'Q9-111', 'name' => 'Balagha Test', 'type' => 'individual', 'is_stage' => false, 'zone' => 'A Zone', 'limit' => 2, 'cat' => 'literature'],
                ['code' => 'Q9-112', 'name' => 'Arabic Story Writing', 'type' => 'individual', 'is_stage' => false, 'zone' => 'A Zone', 'limit' => 2, 'cat' => 'literature'],
                ['code' => 'Q9-113', 'name' => 'Malayalam Story Writing', 'type' => 'individual', 'is_stage' => false, 'zone' => 'A Zone', 'limit' => 3, 'cat' => 'literature'],
                ['code' => 'Q9-114', 'name' => 'English Story Writing', 'type' => 'individual', 'is_stage' => false, 'zone' => 'A Zone', 'limit' => 2, 'cat' => 'literature'],
                ['code' => 'Q9-115', 'name' => 'Arabic Haiku', 'type' => 'individual', 'is_stage' => false, 'zone' => 'A Zone', 'limit' => 2, 'cat' => 'literature'],
                ['code' => 'Q9-116', 'name' => 'English Poem Writing', 'type' => 'individual', 'is_stage' => false, 'zone' => 'A Zone', 'limit' => 2, 'cat' => 'literature'],
                ['code' => 'Q9-117', 'name' => 'Malayalam Poem Writing', 'type' => 'individual', 'is_stage' => false, 'zone' => 'A Zone', 'limit' => 3, 'cat' => 'literature'],
                ['code' => 'Q9-118', 'name' => 'Arabic Essay Writing', 'type' => 'individual', 'is_stage' => false, 'zone' => 'A Zone', 'limit' => 2, 'cat' => 'literature'],
                ['code' => 'Q9-119', 'name' => 'Malayalam Essay Writing', 'type' => 'individual', 'is_stage' => false, 'zone' => 'A Zone', 'limit' => 3, 'cat' => 'literature'],
                ['code' => 'Q9-120', 'name' => 'English Essay Writing', 'type' => 'individual', 'is_stage' => false, 'zone' => 'A Zone', 'limit' => 2, 'cat' => 'literature'],
                ['code' => 'Q9-121', 'name' => 'Mappilappattu Writing', 'type' => 'individual', 'is_stage' => false, 'zone' => 'A Zone', 'limit' => 2, 'cat' => 'literature'],
                ['code' => 'Q9-122', 'name' => 'Blurb Writing', 'type' => 'individual', 'is_stage' => false, 'zone' => 'A Zone', 'limit' => 2, 'cat' => 'literature'],
                ['code' => 'Q9-123', 'name' => 'Photography', 'type' => 'individual', 'is_stage' => false, 'zone' => 'A Zone', 'limit' => 2, 'cat' => 'fine-arts'],
                ['code' => 'Q9-124', 'name' => 'Book Test', 'type' => 'individual', 'is_stage' => false, 'zone' => 'A Zone', 'limit' => 2, 'cat' => 'literature'],
                ['code' => 'Q9-125', 'name' => 'Social Text Malayalam', 'type' => 'individual', 'is_stage' => false, 'zone' => 'A Zone', 'limit' => 3, 'cat' => 'literature'],
                ['code' => 'Q9-126', 'name' => 'Translation Eng to Ara', 'type' => 'individual', 'is_stage' => false, 'zone' => 'A Zone', 'limit' => 2, 'cat' => 'literature'],
                ['code' => 'Q9-127', 'name' => 'Spot Magazine Arabic', 'type' => 'individual', 'is_stage' => false, 'zone' => 'A Zone', 'limit' => 3, 'cat' => 'literature'],

                // B Zone (26 programmes: Q9-128 to Q9-153)
                ['code' => 'Q9-128', 'name' => "Qira'ath", 'type' => 'individual', 'is_stage' => true, 'zone' => 'B Zone', 'limit' => 2, 'cat' => 'vocal-arts'],
                ['code' => 'Q9-129', 'name' => 'Arabic Speech', 'type' => 'individual', 'is_stage' => true, 'zone' => 'B Zone', 'limit' => 2, 'cat' => 'elocution'],
                ['code' => 'Q9-130', 'name' => 'Malayalam Speech', 'type' => 'individual', 'is_stage' => true, 'zone' => 'B Zone', 'limit' => 2, 'cat' => 'elocution'],
                ['code' => 'Q9-131', 'name' => 'English Speech', 'type' => 'individual', 'is_stage' => true, 'zone' => 'B Zone', 'limit' => 2, 'cat' => 'elocution'],
                ['code' => 'Q9-132', 'name' => "Sada'i Nasam", 'type' => 'individual', 'is_stage' => true, 'zone' => 'B Zone', 'limit' => 2, 'cat' => 'vocal-arts'],
                ['code' => 'Q9-133', 'name' => "Na'at", 'type' => 'individual', 'is_stage' => true, 'zone' => 'B Zone', 'limit' => 2, 'cat' => 'vocal-arts'],
                ['code' => 'Q9-134', 'name' => 'Interview Desk', 'type' => 'individual', 'is_stage' => true, 'zone' => 'B Zone', 'limit' => 2, 'cat' => 'stage-arts'],
                ['code' => 'Q9-135', 'name' => 'Ideal Talk', 'type' => 'individual', 'is_stage' => true, 'zone' => 'B Zone', 'limit' => 2, 'cat' => 'elocution'],
                ['code' => 'Q9-136', 'name' => 'Tashreeh', 'type' => 'individual', 'is_stage' => false, 'zone' => 'B Zone', 'limit' => 2, 'cat' => 'literature'],
                ['code' => 'Q9-137', 'name' => 'Thafseer Making', 'type' => 'individual', 'is_stage' => false, 'zone' => 'B Zone', 'limit' => 2, 'cat' => 'literature'],
                ['code' => 'Q9-138', 'name' => 'Tuhfa Talent Test', 'type' => 'individual', 'is_stage' => false, 'zone' => 'B Zone', 'limit' => 2, 'cat' => 'literature'],
                ['code' => 'Q9-139', 'name' => 'Arabic Story Writing', 'type' => 'individual', 'is_stage' => false, 'zone' => 'B Zone', 'limit' => 2, 'cat' => 'literature'],
                ['code' => 'Q9-140', 'name' => 'English Story Writing', 'type' => 'individual', 'is_stage' => false, 'zone' => 'B Zone', 'limit' => 2, 'cat' => 'literature'],
                ['code' => 'Q9-141', 'name' => 'Micro Fiction Malayalam', 'type' => 'individual', 'is_stage' => false, 'zone' => 'B Zone', 'limit' => 3, 'cat' => 'literature'],
                ['code' => 'Q9-142', 'name' => 'Arabic Poem Writing', 'type' => 'individual', 'is_stage' => false, 'zone' => 'B Zone', 'limit' => 2, 'cat' => 'literature'],
                ['code' => 'Q9-143', 'name' => 'English Poem Writing', 'type' => 'individual', 'is_stage' => false, 'zone' => 'B Zone', 'limit' => 2, 'cat' => 'literature'],
                ['code' => 'Q9-144', 'name' => 'Malayalam Poem Writing', 'type' => 'individual', 'is_stage' => false, 'zone' => 'B Zone', 'limit' => 3, 'cat' => 'literature'],
                ['code' => 'Q9-145', 'name' => 'Arabic Essay Writing', 'type' => 'individual', 'is_stage' => false, 'zone' => 'B Zone', 'limit' => 2, 'cat' => 'literature'],
                ['code' => 'Q9-146', 'name' => 'Malayalam Essay Writing', 'type' => 'individual', 'is_stage' => false, 'zone' => 'B Zone', 'limit' => 3, 'cat' => 'literature'],
                ['code' => 'Q9-147', 'name' => 'English Essay Writing', 'type' => 'individual', 'is_stage' => false, 'zone' => 'B Zone', 'limit' => 2, 'cat' => 'literature'],
                ['code' => 'Q9-148', 'name' => 'Revolutionary Song Writing', 'type' => 'individual', 'is_stage' => false, 'zone' => 'B Zone', 'limit' => 2, 'cat' => 'literature'],
                ['code' => 'Q9-149', 'name' => 'Photography', 'type' => 'individual', 'is_stage' => false, 'zone' => 'B Zone', 'limit' => 2, 'cat' => 'fine-arts'],
                ['code' => 'Q9-150', 'name' => 'Book Test', 'type' => 'individual', 'is_stage' => false, 'zone' => 'B Zone', 'limit' => 2, 'cat' => 'literature'],
                ['code' => 'Q9-151', 'name' => 'Social Text Malayalam', 'type' => 'individual', 'is_stage' => false, 'zone' => 'B Zone', 'limit' => 5, 'cat' => 'literature'],
                ['code' => 'Q9-152', 'name' => 'Translation Arab to Eng', 'type' => 'individual', 'is_stage' => false, 'zone' => 'B Zone', 'limit' => 2, 'cat' => 'literature'],
                ['code' => 'Q9-153', 'name' => 'Spot Magazine English', 'type' => 'individual', 'is_stage' => false, 'zone' => 'B Zone', 'limit' => 5, 'cat' => 'literature'],

                // C Zone (22 programmes: Q9-154 to Q9-175)
                ['code' => 'Q9-154', 'name' => "Qira'ath", 'type' => 'individual', 'is_stage' => true, 'zone' => 'C Zone', 'limit' => 2, 'cat' => 'vocal-arts'],
                ['code' => 'Q9-155', 'name' => 'Arabic Speech', 'type' => 'individual', 'is_stage' => true, 'zone' => 'C Zone', 'limit' => 2, 'cat' => 'elocution'],
                ['code' => 'Q9-156', 'name' => 'Malayalam Speech', 'type' => 'individual', 'is_stage' => true, 'zone' => 'C Zone', 'limit' => 2, 'cat' => 'elocution'],
                ['code' => 'Q9-157', 'name' => 'English Speech', 'type' => 'individual', 'is_stage' => true, 'zone' => 'C Zone', 'limit' => 2, 'cat' => 'elocution'],
                ['code' => 'Q9-158', 'name' => 'Madh Ganam', 'type' => 'individual', 'is_stage' => true, 'zone' => 'C Zone', 'limit' => 2, 'cat' => 'vocal-arts'],
                ['code' => 'Q9-159', 'name' => "Imla'", 'type' => 'individual', 'is_stage' => false, 'zone' => 'C Zone', 'limit' => 2, 'cat' => 'literature'],
                ['code' => 'Q9-160', 'name' => 'Tahfeezul Alfiyya', 'type' => 'individual', 'is_stage' => false, 'zone' => 'C Zone', 'limit' => 2, 'cat' => 'literature'],
                ['code' => 'Q9-161', 'name' => 'Arabic Story Writing', 'type' => 'individual', 'is_stage' => false, 'zone' => 'C Zone', 'limit' => 2, 'cat' => 'literature'],
                ['code' => 'Q9-162', 'name' => 'English Story Writing', 'type' => 'individual', 'is_stage' => false, 'zone' => 'C Zone', 'limit' => 2, 'cat' => 'literature'],
                ['code' => 'Q9-163', 'name' => 'Malayalam Story Writing', 'type' => 'individual', 'is_stage' => false, 'zone' => 'C Zone', 'limit' => 3, 'cat' => 'literature'],
                ['code' => 'Q9-164', 'name' => 'Arabic Poem Writing', 'type' => 'individual', 'is_stage' => false, 'zone' => 'C Zone', 'limit' => 2, 'cat' => 'literature'],
                ['code' => 'Q9-165', 'name' => 'Malayalam Poem Writing', 'type' => 'individual', 'is_stage' => false, 'zone' => 'C Zone', 'limit' => 3, 'cat' => 'literature'],
                ['code' => 'Q9-166', 'name' => 'English Poem Writing', 'type' => 'individual', 'is_stage' => false, 'zone' => 'C Zone', 'limit' => 2, 'cat' => 'literature'],
                ['code' => 'Q9-167', 'name' => 'Arabic Essay Writing', 'type' => 'individual', 'is_stage' => false, 'zone' => 'C Zone', 'limit' => 2, 'cat' => 'literature'],
                ['code' => 'Q9-168', 'name' => 'Malayalam Essay Writing', 'type' => 'individual', 'is_stage' => false, 'zone' => 'C Zone', 'limit' => 3, 'cat' => 'literature'],
                ['code' => 'Q9-169', 'name' => 'English Essay Writing', 'type' => 'individual', 'is_stage' => false, 'zone' => 'C Zone', 'limit' => 2, 'cat' => 'literature'],
                ['code' => 'Q9-170', 'name' => 'Slogan Writing', 'type' => 'individual', 'is_stage' => false, 'zone' => 'C Zone', 'limit' => 2, 'cat' => 'literature'],
                ['code' => 'Q9-171', 'name' => 'Photography', 'type' => 'individual', 'is_stage' => false, 'zone' => 'C Zone', 'limit' => 2, 'cat' => 'fine-arts'],
                ['code' => 'Q9-172', 'name' => 'Book Test', 'type' => 'individual', 'is_stage' => false, 'zone' => 'C Zone', 'limit' => 2, 'cat' => 'literature'],
                ['code' => 'Q9-173', 'name' => 'Social Text Malayalam', 'type' => 'individual', 'is_stage' => false, 'zone' => 'C Zone', 'limit' => 3, 'cat' => 'literature'],
                ['code' => 'Q9-174', 'name' => 'Translation Malayalam to Arabic', 'type' => 'individual', 'is_stage' => false, 'zone' => 'C Zone', 'limit' => 2, 'cat' => 'literature'],
                ['code' => 'Q9-175', 'name' => 'Wall Magazine', 'type' => 'individual', 'is_stage' => false, 'zone' => 'C Zone', 'limit' => 3, 'cat' => 'fine-arts'],

                // Mix Zone (69 programmes: Q9-176 to Q9-244)
                ['code' => 'Q9-176', 'name' => 'Global Dars', 'type' => 'individual', 'is_stage' => true, 'zone' => 'Mix Zone', 'limit' => 2, 'cat' => 'stage-arts'],
                ['code' => 'Q9-177', 'name' => "Wa'z", 'type' => 'individual', 'is_stage' => true, 'zone' => 'Mix Zone', 'limit' => 2, 'cat' => 'elocution'],
                ['code' => 'Q9-178', 'name' => 'Mala Aavishkaram', 'type' => 'individual', 'is_stage' => true, 'zone' => 'Mix Zone', 'limit' => 2, 'cat' => 'vocal-arts'],
                ['code' => 'Q9-179', 'name' => 'Live Extempore', 'type' => 'individual', 'is_stage' => true, 'zone' => 'Mix Zone', 'limit' => 2, 'cat' => 'elocution'],
                ['code' => 'Q9-180', 'name' => 'Bilingual Speech', 'type' => 'individual', 'is_stage' => true, 'zone' => 'Mix Zone', 'limit' => 2, 'cat' => 'elocution'],
                ['code' => 'Q9-181', 'name' => 'Lecturing', 'type' => 'individual', 'is_stage' => true, 'zone' => 'Mix Zone', 'limit' => 2, 'cat' => 'elocution'],
                ['code' => 'Q9-182', 'name' => 'Paper Presentation', 'type' => 'individual', 'is_stage' => true, 'zone' => 'Mix Zone', 'limit' => 2, 'cat' => 'elocution'],
                ['code' => 'Q9-183', 'name' => 'Media Critic', 'type' => 'individual', 'is_stage' => true, 'zone' => 'Mix Zone', 'limit' => 2, 'cat' => 'stage-arts'],
                ['code' => 'Q9-184', 'name' => 'Descriptive Quiz', 'type' => 'individual', 'is_stage' => true, 'zone' => 'Mix Zone', 'limit' => 2, 'cat' => 'stage-arts'],
                ['code' => 'Q9-185', 'name' => 'Hamd Urdu', 'type' => 'individual', 'is_stage' => true, 'zone' => 'Mix Zone', 'limit' => 2, 'cat' => 'vocal-arts'],
                ['code' => 'Q9-186', 'name' => 'Mappilappattu', 'type' => 'individual', 'is_stage' => true, 'zone' => 'Mix Zone', 'limit' => 2, 'cat' => 'vocal-arts'],
                ['code' => 'Q9-187', 'name' => 'Social Debate', 'type' => 'individual', 'is_stage' => true, 'zone' => 'Mix Zone', 'limit' => 2, 'cat' => 'elocution'],
                ['code' => 'Q9-188', 'name' => 'Tahfeezul Alfiyya (A&B)', 'type' => 'individual', 'is_stage' => true, 'zone' => 'Mix Zone', 'limit' => 2, 'cat' => 'vocal-arts'],
                ['code' => 'Q9-189', 'name' => 'Tahfeezul Burda', 'type' => 'individual', 'is_stage' => true, 'zone' => 'Mix Zone', 'limit' => 2, 'cat' => 'vocal-arts'],
                ['code' => 'Q9-190', 'name' => 'Q Talk', 'type' => 'group', 'is_stage' => true, 'zone' => 'Mix Zone', 'limit' => 2, 'cat' => 'stage-arts'],
                ['code' => 'Q9-191', 'name' => 'Munazara', 'type' => 'group', 'is_stage' => true, 'zone' => 'Mix Zone', 'limit' => 5, 'cat' => 'stage-arts'],
                ['code' => 'Q9-192', 'name' => 'Fiqh Colloquium', 'type' => 'group', 'is_stage' => true, 'zone' => 'Mix Zone', 'limit' => 3, 'cat' => 'stage-arts'],
                ['code' => 'Q9-193', 'name' => 'Book Discussion', 'type' => 'group', 'is_stage' => true, 'zone' => 'Mix Zone', 'limit' => 4, 'cat' => 'stage-arts'],
                ['code' => 'Q9-194', 'name' => 'Press Meet', 'type' => 'group', 'is_stage' => true, 'zone' => 'Mix Zone', 'limit' => 4, 'cat' => 'stage-arts'],
                ['code' => 'Q9-195', 'name' => 'Qaseeda', 'type' => 'group', 'is_stage' => true, 'zone' => 'Mix Zone', 'limit' => 4, 'cat' => 'vocal-arts'],
                ['code' => 'Q9-196', 'name' => 'Nasheeda', 'type' => 'group', 'is_stage' => true, 'zone' => 'Mix Zone', 'limit' => 5, 'cat' => 'vocal-arts'],
                ['code' => 'Q9-197', 'name' => 'Qawwali', 'type' => 'group', 'is_stage' => true, 'zone' => 'Mix Zone', 'limit' => 5, 'cat' => 'vocal-arts'],
                ['code' => 'Q9-198', 'name' => 'Group Song', 'type' => 'group', 'is_stage' => true, 'zone' => 'Mix Zone', 'limit' => 4, 'cat' => 'vocal-arts'],
                ['code' => 'Q9-199', 'name' => 'Rihla Discussion', 'type' => 'group', 'is_stage' => true, 'zone' => 'Mix Zone', 'limit' => 2, 'cat' => 'stage-arts'],
                ['code' => 'Q9-200', 'name' => "Iftaa'", 'type' => 'individual', 'is_stage' => false, 'zone' => 'Mix Zone', 'limit' => 2, 'cat' => 'literature'],
                ['code' => 'Q9-201', 'name' => "Fara'id Test", 'type' => 'individual', 'is_stage' => false, 'zone' => 'Mix Zone', 'limit' => 1, 'cat' => 'literature'],
                ['code' => 'Q9-202', 'name' => 'Theology Test', 'type' => 'individual', 'is_stage' => false, 'zone' => 'Mix Zone', 'limit' => 1, 'cat' => 'literature'],
                ['code' => 'Q9-203', 'name' => 'Genius Hunt', 'type' => 'individual', 'is_stage' => false, 'zone' => 'Mix Zone', 'limit' => 2, 'cat' => 'literature'],
                ['code' => 'Q9-204', 'name' => 'Abstract', 'type' => 'individual', 'is_stage' => false, 'zone' => 'Mix Zone', 'limit' => 2, 'cat' => 'literature'],
                ['code' => 'Q9-205', 'name' => 'Urdu Story Writing', 'type' => 'individual', 'is_stage' => false, 'zone' => 'Mix Zone', 'limit' => 2, 'cat' => 'literature'],
                ['code' => 'Q9-206', 'name' => 'Urdu Essay Writing', 'type' => 'individual', 'is_stage' => false, 'zone' => 'Mix Zone', 'limit' => 2, 'cat' => 'literature'],
                ['code' => 'Q9-207', 'name' => 'Madh Song Writing', 'type' => 'individual', 'is_stage' => false, 'zone' => 'Mix Zone', 'limit' => 2, 'cat' => 'literature'],
                ['code' => 'Q9-208', 'name' => 'Social Text English', 'type' => 'individual', 'is_stage' => false, 'zone' => 'Mix Zone', 'limit' => 2, 'cat' => 'literature'],
                ['code' => 'Q9-209', 'name' => 'Theme Song Writing', 'type' => 'individual', 'is_stage' => false, 'zone' => 'Mix Zone', 'limit' => 2, 'cat' => 'literature'],
                ['code' => 'Q9-210', 'name' => 'Motto Making', 'type' => 'individual', 'is_stage' => false, 'zone' => 'Mix Zone', 'limit' => 2, 'cat' => 'literature'],
                ['code' => 'Q9-211', 'name' => 'Feature Writing', 'type' => 'individual', 'is_stage' => false, 'zone' => 'Mix Zone', 'limit' => 2, 'cat' => 'literature'],
                ['code' => 'Q9-212', 'name' => 'AI Poem', 'type' => 'individual', 'is_stage' => false, 'zone' => 'Mix Zone', 'limit' => 2, 'cat' => 'literature'],
                ['code' => 'Q9-213', 'name' => 'Translation Urdu to Arabic', 'type' => 'individual', 'is_stage' => false, 'zone' => 'Mix Zone', 'limit' => 2, 'cat' => 'literature'],
                ['code' => 'Q9-214', 'name' => 'Poem Translation Arabic to Malayalam', 'type' => 'individual', 'is_stage' => false, 'zone' => 'Mix Zone', 'limit' => 2, 'cat' => 'literature'],
                ['code' => 'Q9-215', 'name' => 'Calligraphy', 'type' => 'individual', 'is_stage' => false, 'zone' => 'Mix Zone', 'limit' => 2, 'cat' => 'fine-arts'],
                ['code' => 'Q9-216', 'name' => 'Digital Designing', 'type' => 'individual', 'is_stage' => false, 'zone' => 'Mix Zone', 'limit' => 2, 'cat' => 'fine-arts'],
                ['code' => 'Q9-217', 'name' => 'Poster Designing', 'type' => 'individual', 'is_stage' => false, 'zone' => 'Mix Zone', 'limit' => 2, 'cat' => 'fine-arts'],
                ['code' => 'Q9-218', 'name' => 'Vlog Malayalam', 'type' => 'individual', 'is_stage' => false, 'zone' => 'Mix Zone', 'limit' => 2, 'cat' => 'fine-arts'],
                ['code' => 'Q9-219', 'name' => 'Vlog Arabic', 'type' => 'individual', 'is_stage' => false, 'zone' => 'Mix Zone', 'limit' => 2, 'cat' => 'fine-arts'],
                ['code' => 'Q9-220', 'name' => 'E-Poster', 'type' => 'individual', 'is_stage' => false, 'zone' => 'Mix Zone', 'limit' => 2, 'cat' => 'fine-arts'],
                ['code' => 'Q9-221', 'name' => 'Magazine Layout', 'type' => 'individual', 'is_stage' => false, 'zone' => 'Mix Zone', 'limit' => 2, 'cat' => 'fine-arts'],
                ['code' => 'Q9-222', 'name' => 'Reel Making English', 'type' => 'individual', 'is_stage' => false, 'zone' => 'Mix Zone', 'limit' => 2, 'cat' => 'fine-arts'],
                ['code' => 'Q9-223', 'name' => 'Typography', 'type' => 'individual', 'is_stage' => false, 'zone' => 'Mix Zone', 'limit' => 2, 'cat' => 'fine-arts'],
                ['code' => 'Q9-224', 'name' => 'Kitab Quiz', 'type' => 'individual', 'is_stage' => false, 'zone' => 'Mix Zone', 'limit' => 2, 'cat' => 'literature'],
                ['code' => 'Q9-225', 'name' => 'AI Short Video', 'type' => 'individual', 'is_stage' => false, 'zone' => 'Mix Zone', 'limit' => 2, 'cat' => 'fine-arts'],
                ['code' => 'Q9-226', 'name' => 'Tarjuma', 'type' => 'individual', 'is_stage' => false, 'zone' => 'Mix Zone', 'limit' => 2, 'cat' => 'literature'],
                ['code' => 'Q9-227', 'name' => 'AI Portfolio Design', 'type' => 'individual', 'is_stage' => false, 'zone' => 'Mix Zone', 'limit' => 2, 'cat' => 'fine-arts'],
                ['code' => 'Q9-228', 'name' => 'Tajweed Test', 'type' => 'individual', 'is_stage' => false, 'zone' => 'Mix Zone', 'limit' => 2, 'cat' => 'literature'],
                ['code' => 'Q9-229', 'name' => 'Content Writing', 'type' => 'individual', 'is_stage' => false, 'zone' => 'Mix Zone', 'limit' => 2, 'cat' => 'literature'],
                ['code' => 'Q9-230', 'name' => 'Catalogue Making', 'type' => 'individual', 'is_stage' => false, 'zone' => 'Mix Zone', 'limit' => 1, 'cat' => 'fine-arts'],
                ['code' => 'Q9-231', 'name' => 'Hadith Musabaka', 'type' => 'group', 'is_stage' => false, 'zone' => 'Mix Zone', 'limit' => 3, 'cat' => 'literature'],
                ['code' => 'Q9-232', 'name' => 'Tasneef', 'type' => 'group', 'is_stage' => false, 'zone' => 'Mix Zone', 'limit' => 5, 'cat' => 'literature'],
                ['code' => 'Q9-233', 'name' => 'Podcast', 'type' => 'group', 'is_stage' => false, 'zone' => 'Mix Zone', 'limit' => 2, 'cat' => 'fine-arts'],
                ['code' => 'Q9-234', 'name' => 'Book Writing', 'type' => 'group', 'is_stage' => false, 'zone' => 'Mix Zone', 'limit' => 5, 'cat' => 'literature'],
                ['code' => 'Q9-235', 'name' => 'Wall Writing / Graffiti', 'type' => 'group', 'is_stage' => false, 'zone' => 'Mix Zone', 'limit' => 2, 'cat' => 'fine-arts'],
                ['code' => 'Q9-236', 'name' => 'Insight', 'type' => 'group', 'is_stage' => false, 'zone' => 'Mix Zone', 'limit' => 1, 'cat' => 'fine-arts'],
                ['code' => 'Q9-237', 'name' => 'Documentary', 'type' => 'group', 'is_stage' => false, 'zone' => 'Mix Zone', 'limit' => 2, 'cat' => 'fine-arts'],
                ['code' => 'Q9-238', 'name' => 'Project', 'type' => 'group', 'is_stage' => false, 'zone' => 'Mix Zone', 'limit' => 5, 'cat' => 'stage-arts'],
                ['code' => 'Q9-239', 'name' => 'Master Plan', 'type' => 'group', 'is_stage' => false, 'zone' => 'Mix Zone', 'limit' => 3, 'cat' => 'stage-arts'],
                ['code' => 'Q9-240', 'name' => 'Interview Making', 'type' => 'group', 'is_stage' => false, 'zone' => 'Mix Zone', 'limit' => 3, 'cat' => 'fine-arts'],
                ['code' => 'Q9-241', 'name' => 'Centenary Footprint', 'type' => 'group', 'is_stage' => false, 'zone' => 'Mix Zone', 'limit' => 1, 'cat' => 'fine-arts'],
                ['code' => 'Q9-242', 'name' => 'Visual Story', 'type' => 'group', 'is_stage' => false, 'zone' => 'Mix Zone', 'limit' => 2, 'cat' => 'fine-arts'],
                ['code' => 'Q9-243', 'name' => 'Photo Feature', 'type' => 'group', 'is_stage' => false, 'zone' => 'Mix Zone', 'limit' => 2, 'cat' => 'fine-arts'],
                ['code' => 'Q9-244', 'name' => 'Capture the Flag', 'type' => 'group', 'is_stage' => false, 'zone' => 'Mix Zone', 'limit' => 2, 'cat' => 'stage-arts'],
            ];

            $csvPath = base_path('database/data/Programme_List_quaf26.csv');
            if (file_exists($csvPath) && ($handle = fopen($csvPath, 'r')) !== false) {
                fgetcsv($handle); // skip header
                $csvData = [];
                while (($row = fgetcsv($handle)) !== false) {
                    if (empty($row[0]) || ! is_numeric($row[0])) {
                        continue;
                    }
                    $rawCode = trim(preg_replace('/\s+/', '', (string) ($row[1] ?? '')));
                    if (str_starts_with($rawCode, 'Q9') && ! str_contains($rawCode, '-')) {
                        $rawCode = preg_replace('/^Q9(\d+)/', 'Q9-$1', $rawCode);
                    }
                    $csvData[$rawCode] = [
                        'name' => trim((string) ($row[2] ?? '')),
                        'type' => strtolower(trim((string) ($row[3] ?? ''))) === 'group' ? 'group' : 'individual',
                        'is_stage' => strtolower(trim((string) ($row[4] ?? ''))) === 'stage',
                        'zone' => trim((string) ($row[5] ?? 'A Zone')),
                        'limit' => max(1, (int) trim((string) ($row[6] ?? '1'))),
                    ];
                }
                fclose($handle);

                // Overlay CSV details onto programsList
                foreach ($programsList as &$item) {
                    if (isset($csvData[$item['code']])) {
                        $c = $csvData[$item['code']];
                        $item['name'] = $c['name'];
                        $item['type'] = $c['type'];
                        $item['is_stage'] = $c['is_stage'];
                        $item['zone'] = $c['zone'];
                        $item['limit'] = $c['limit'];
                    }
                }
                unset($item);
            }

            $count = 0;
            $force = (bool) $this->option('force');

            foreach ($programsList as $p) {
                $isGroup = ($p['type'] === 'group');
                $totalMax = $isGroup ? ($p['limit'] * 5) : ($p['limit'] * 5);

                $existing = Program::where('code', $p['code'])->first();
                if ($existing) {
                    if ($force) {
                        $existing->update([
                            'name' => $p['name'],
                            'type' => $p['type'],
                            'is_stage' => $p['is_stage'],
                            'eligibility' => $p['zone'],
                            'zone_id' => $zones[$p['zone']] ?? 1,
                            'category_id' => $categories[$p['cat']]->id,
                            'stage_id' => $p['is_stage'] ? ($mainStage->id ?? 1) : null,
                            'participant_count' => $p['limit'],
                            'max_participants_per_group' => $isGroup ? 1 : $p['limit'],
                            'max_participants' => $totalMax,
                            'status' => 'upcoming',
                            'duration_minutes' => 30,
                            'points_weight' => 1.00,
                        ]);
                    }
                    $count++;

                    continue;
                }

                Program::create([
                    'code' => $p['code'],
                    'name' => $p['name'],
                    'type' => $p['type'],
                    'is_stage' => $p['is_stage'],
                    'eligibility' => $p['zone'],
                    'zone_id' => $zones[$p['zone']] ?? 1,
                    'category_id' => $categories[$p['cat']]->id,
                    'stage_id' => $p['is_stage'] ? ($mainStage->id ?? 1) : null,
                    'participant_count' => $p['limit'],
                    'max_participants_per_group' => $isGroup ? 1 : $p['limit'],
                    'max_participants' => $totalMax,
                    'status' => 'upcoming',
                    'duration_minutes' => 30,
                    'points_weight' => 1.00,
                ]);
                $count++;
            }

            Cache::flush();

            $this->info("Successfully synced all {$count} official programs!");
        });

        return Command::SUCCESS;
    }
}
