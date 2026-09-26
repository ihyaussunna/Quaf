# QUAF Fest 09 — Codebase Directory & File Structure
**Organization, Architectural Responsibilities, and Code Map**

---

```text
E:\Desktop\Quaf 9.0/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/                           # Central Administration Controllers
│   │   │   │   ├── AnnouncementController.php   # Broadcast notifications & alerts
│   │   │   │   ├── AuditLogController.php       # System audit trail inspector
│   │   │   │   ├── CertificateController.php    # Certificate generation & signing
│   │   │   │   ├── CodeLetterController.php     # Anonymized contestant sequencing
│   │   │   │   ├── DashboardController.php      # Main analytics & performance charts
│   │   │   │   ├── ExportController.php         # Printable gazette & exports
│   │   │   │   ├── FormController.php           # Call lists & judge evaluation sheets
│   │   │   │   ├── GalleryController.php        # Photo gallery management
│   │   │   │   ├── GroupController.php          # Academic house / team management
│   │   │   │   ├── IdCardController.php         # Chest slips & ID badges
│   │   │   │   ├── JudgeController.php          # Jury empanelling & assignments
│   │   │   │   ├── MarkEntryController.php      # Marks checking & tabulation
│   │   │   │   ├── NewsController.php           # Editorial journal dispatches
│   │   │   │   ├── PointController.php          # Points configuration & weights
│   │   │   │   ├── PrintReportController.php    # PDF & printable builders
│   │   │   │   ├── ProfileController.php        # Admin profile settings
│   │   │   │   ├── ProgramController.php        # Competition master & limits
│   │   │   │   ├── RegistrationController.php   # Enrollment review & approvals
│   │   │   │   ├── ResultController.php         # Result declaration & publication
│   │   │   │   ├── ScheduleController.php       # Stage & time orchestration
│   │   │   │   ├── SettingController.php        # Sliding settings drawer handler
│   │   │   │   ├── StageController.php          # Venue configuration & live states
│   │   │   │   ├── StudentController.php        # Student master & 360 profile
│   │   │   │   ├── TemplateController.php       # Document templates
│   │   │   │   ├── TopScorerController.php      # Championships & zone scoreboards
│   │   │   │   ├── VideoController.php          # Video archive & streams
│   │   │   │   ├── WebsiteBuilderController.php # Content management
│   │   │   │   └── ZoneController.php           # Dedicated 4-Zone Dashboard & metrics
│   │   │   ├── Announcer/                       # Stage announcer console
│   │   │   ├── Auth/                            # Authentication & session controllers
│   │   │   ├── GreenRoom/                       # Green room check-in & call slates
│   │   │   ├── Judge/                           # Jury digital scorecard evaluation
│   │   │   ├── Leader/                          # Academic house captain portal
│   │   │   └── Public/                          # Public festival homepage, news, gallery
│   │   └── Middleware/
│   │       └── RoleMiddleware.php               # Multi-role access control gateway
│   ├── Models/                                  # Eloquent ORM Data Models
│   │   ├── Announcement.php
│   │   ├── AuditLog.php
│   │   ├── Certificate.php
│   │   ├── FestivalSetting.php
│   │   ├── GalleryItem.php
│   │   ├── Group.php                            # Academic House model
│   │   ├── Judge.php
│   │   ├── JudgeAssignment.php
│   │   ├── News.php
│   │   ├── Program.php                          # Competition model with 4 ZONES
│   │   ├── ProgramCategory.php
│   │   ├── ProgramEntry.php                     # Contestant registration bridge
│   │   ├── Result.php                           # Official verdict model
│   │   ├── Schedule.php
│   │   ├── ScoreSheet.php                       # Judge criteria marks
│   │   ├── ScoringCriteria.php
│   │   ├── Stage.php                            # Stage venue model
│   │   ├── Student.php                          # Competitor model with 4 ZONES
│   │   ├── User.php                             # System user model
│   │   └── VideoItem.php
│   └── Services/
│       └── AuditLogger.php                      # Immutable audit logging engine
│
├── config/                                      # Laravel configuration files
│   ├── app.php
│   ├── auth.php
│   ├── database.php
│   └── ...
│
├── database/
│   ├── database.sqlite                          # Primary SQLite database file
│   ├── factories/                               # Model test factories
│   ├── migrations/                              # Database schema migrations
│   └── seeders/                                 # Initial data & demo seeders
│
├── public/
│   ├── build/                                   # Compiled Vite assets (CSS/JS)
│   ├── images/                                  # Official logo & brand assets
│   │   ├── quaf-title-logo.png                  # Official festival title logo
│   │   ├── dashboard-logo.png                   # Header navbar identity logo
│   │   └── favicon.png                          # Site icon
│   └── index.php                                # HTTP gateway entry point
│
├── resources/
│   ├── css/
│   │   └── app.css                              # Tailwind CSS v4 directives
│   ├── js/
│   │   ├── app.js                               # Alpine.js & client scripts
│   │   └── bootstrap.js
│   └── views/                                   # Blade Templates
│       ├── layouts/
│       │   ├── admin.blade.php                  # Central Admin Shell + Settings Drawer
│       │   ├── public.blade.php                 # Public Site Shell + Brand Footer
│       │   ├── leader.blade.php                 # House Leader Shell
│       │   └── judge.blade.php                  # Touch-friendly Judge Shell
│       ├── admin/                               # Admin module views (zones, marks, etc.)
│       │   └── zones/index.blade.php            # 4-Zone Dashboard View
│       ├── public/                              # Public views (home, results, etc.)
│       │   └── home.blade.php                   # Homepage with Hero, Houses, Zones, Stages
│       ├── leader/                              # House Leader views
│       ├── judge/                               # Jury score sheets
│       └── greenroom/                           # Green Room call sheets
│
├── routes/
│   ├── console.php                              # Artisan commands
│   └── web.php                                  # All application HTTP routes
│
└── tests/
    └── Feature/                                 # PHPUnit Feature Test Suite
        ├── AdminViewsTest.php                   # All admin route regression tests
        ├── AdminZoneTest.php                    # Zone Dashboard & Homepage tests
        ├── QuafSeason09WorkflowTest.php         # End-to-end festival workflow
        └── RegistrationChestNumberTest.php      # Chest number constraint tests
```
