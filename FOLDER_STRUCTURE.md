# QUAF Fest 09 — Codebase Directory & File Structure
**Organization, Architectural Responsibilities, and Code Map**

---

```text
E:\Desktop\Quaf 9.0/
├── app/
│   ├── Console/
│   │   └── Commands/
│   │       ├── SyncOfficialProgramsCommand.php   # CLI command to sync 144 official programs
│   │       └── SyncOfficialStudentsCommand.php   # CLI command to sync 1,168 official students
│   │
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/                            # Central Administration Controllers
│   │   │   │   ├── AchievementController.php     # House standings & scoreboards
│   │   │   │   ├── AnnouncementController.php    # Emergency alerts & broadcast bar
│   │   │   │   ├── AuditLogController.php        # Audit trail logs viewer
│   │   │   │   ├── CertificateController.php     # Certificate generation & bulk actions
│   │   │   │   ├── CodeLetterController.php      # Anonymized code letter matrix
│   │   │   │   ├── DashboardController.php       # Operational dashboard & sync festival data
│   │   │   │   ├── ExportController.php          # Gazette and data exports
│   │   │   │   ├── FormController.php            # Call lists & judge evaluation sheets
│   │   │   │   ├── GalleryController.php         # Photo gallery manager
│   │   │   │   ├── GroupController.php           # 5 Academic groups management
│   │   │   │   ├── IdCardController.php          # Printable chest slips & competitor badges
│   │   │   │   ├── JudgeController.php           # Jury member directory & assignments
│   │   │   │   ├── MarkEntryController.php       # Administrative marksheets & variance checker
│   │   │   │   ├── NewsController.php            # Editorial dispatches manager
│   │   │   │   ├── PointController.php           # Points configuration & ledger inspector
│   │   │   │   ├── PrintReportController.php     # Printable PDF builders
│   │   │   │   ├── ProfileController.php         # Admin account settings
│   │   │   │   ├── ProgramController.php         # 144 Programs catalog & criteria
│   │   │   │   ├── RegistrationController.php    # Entries directory & reject/approve actions
│   │   │   │   ├── ResultController.php          # Declare, publish, and undeclare results
│   │   │   │   ├── ScheduleController.php        # Stage & time orchestration
│   │   │   │   ├── SettingController.php         # Sliding settings drawer handler
│   │   │   │   ├── StageController.php           # Stage venues, live status & projector
│   │   │   │   ├── StudentController.php         # Competitor master & bulk enrollment
│   │   │   │   ├── SystemController.php          # System tools & official data sync
│   │   │   │   ├── TemplateController.php        # Document templates
│   │   │   │   ├── TopScorerController.php       # Kalaprathibha, Kalathilakam, Zone toppers
│   │   │   │   ├── VideoController.php           # Video streams manager
│   │   │   │   ├── WebsiteBuilderController.php  # Public site builder
│   │   │   │   └── ZoneController.php            # Dedicated 4-Zone Dashboard & metrics
│   │   │   │
│   │   │   ├── Announcer/
│   │   │   │   └── AnnouncerController.php       # Stage announcer broadcast console
│   │   │   ├── Auth/
│   │   │   │   └── AuthController.php            # Multi-portal login, logout, password rehashing
│   │   │   ├── GreenRoom/
│   │   │   │   └── GreenRoomController.php       # Check-in, code letters, and stage sequencing
│   │   │   ├── Judge/
│   │   │   │   └── JudgeController.php           # Digital scorecards & PIN authentication
│   │   │   ├── Leader/
│   │   │   │   └── LeaderController.php          # Group leader portal, quota tracking, inline edit
│   │   │   ├── Media/
│   │   │   │   ├── MediaController.php           # News, gallery, video administration
│   │   │   │   └── MediaResultController.php     # Result Poster Studio & canvas publisher
│   │   │   ├── ProgramCommittee/
│   │   │   │   └── ProgramCommitteeController.php# Program guidelines & Niyamavali print engine
│   │   │   ├── Public/
│   │   │   │   ├── HomeController.php            # Public homepage & live scoreboard
│   │   │   │   ├── NewsController.php            # Public news articles
│   │   │   │   ├── ResultController.php          # Public verdicts & podium placement
│   │   │   │   ├── VerificationController.php    # QR certificate & student verification
│   │   │   │   └── VideoController.php           # Video streams & live archive
│   │   │   └── Student/
│   │   │       └── StudentController.php         # Student competitor portal & ID card
│   │   │
│   │   └── Middleware/
│   │       └── RoleMiddleware.php                # Multi-role access control gateway
│   │
│   ├── Models/                                   # Eloquent ORM Data Models
│   │   ├── Announcement.php                      # Emergency broadcast model
│   │   ├── AuditLog.php                          # Immutable audit record model
│   │   ├── Certificate.php                       # Verification certificate model
│   │   ├── FestivalSetting.php                   # Key-value site settings model
│   │   ├── GalleryItem.php                       # Photo gallery item model
│   │   ├── GreenRoomCall.php                     # Stage call queue model
│   │   ├── Group.php                             # Academic Group model (5 groups)
│   │   ├── Judge.php                             # Jury member model
│   │   ├── JudgeAssignment.php                   # Judge-to-program allocation model
│   │   ├── MediaGallery.php                      # Media desk photo model
│   │   ├── MediaNews.php                         # Media desk news article model
│   │   ├── MediaTemplate.php                     # Poster canvas layout template model
│   │   ├── MediaVideo.php                        # Media desk video stream model
│   │   ├── News.php                              # Editorial journal article model
│   │   ├── PointSetting.php                      # Global point weights model
│   │   ├── PointsTransaction.php                 # Auditable point ledger model
│   │   ├── Program.php                           # Competition model (144 programs)
│   │   ├── ProgramCategory.php                   # Discipline discipline model
│   │   ├── ProgramEntry.php                      # Registration bridge model
│   │   ├── ProgramEntryParticipant.php           # Group program participant pivot
│   │   ├── Result.php                            # Official verdict & ranking model
│   │   ├── ResultPoster.php                      # Generated social media graphic poster
│   │   ├── Schedule.php                          # Stage timetable schedule model
│   │   ├── ScoreSheet.php                        # Individual judge marksheet model
│   │   ├── ScoringCriteria.php                   # Evaluation rubric criterion model
│   │   ├── Stage.php                             # Venue & auditorium stage model
│   │   ├── Student.php                           # Student competitor model (1,168 students)
│   │   ├── User.php                              # System user & authorization model
│   │   ├── VideoItem.php                         # Public video model
│   │   └── Zone.php                              # Official 4-Zone model
│   │
│   └── Services/
│       ├── AuditLogger.php                       # Security audit logging service
│       ├── EligibilityService.php                # 10-check transactional enrollment validator
│       └── PointCalculationService.php           # Dense ranking & point recalculation engine
│
├── config/                                       # Framework configurations (auth, database, etc.)
│
├── database/
│   ├── database.sqlite                           # Primary SQLite database file
│   ├── migrations/                               # Database schema migrations
│   └── seeders/                                  # DatabaseSeeder & official sync seeders
│
├── public/
│   ├── build/                                    # Compiled Vite assets (CSS/JS manifests)
│   ├── fonts/                                    # Local webfonts: Rockwell & Malayalam Sangam MN
│   ├── images/                                   # Brand assets: logos, emblems, favicons
│   └── index.php                                 # HTTP gateway entry point
│
├── resources/
│   ├── css/
│   │   └── app.css                               # Tailwind CSS v4 tokens & typography definitions
│   ├── js/
│   │   ├── app.js                                # Alpine.js initialization & safe script switching
│   │   └── bootstrap.js                          # Axios HTTP client configuration
│   └── views/                                    # Blade Templates
│       ├── admin/                                # 20+ Admin dashboard views & drawers
│       ├── announcer/                            # Announcer desk views
│       ├── errors/                               # Error templates (403, 404, 500)
│       ├── greenroom/                            # Green room check-in & stage call views
│       ├── judge/                                # Jury scorecard evaluation views
│       ├── layouts/                              # Master layouts: admin, public, leader, etc.
│       ├── leader/                               # Group leader portal & registration views
│       ├── media/                                # Media desk & Result Poster Studio views
│       ├── program-committee/                    # Program rules & Niyamavali print views
│       ├── public/                               # Public homepage, results, news, gallery
│       └── student/                              # Student portal & digital ID card views
│
└── routes/
    ├── console.php                               # Artisan console commands
    └── web.php                                   # Web routing matrix (250+ routes)
```
