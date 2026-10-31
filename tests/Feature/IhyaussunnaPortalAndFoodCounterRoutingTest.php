<?php

namespace Tests\Feature;

use Tests\TestCase;

class IhyaussunnaPortalAndFoodCounterRoutingTest extends TestCase
{
    public function test_ihyaussunna_root_index_contains_coming_soon_and_clean_foodcounter_routes(): void
    {
        $rootIndexPath = base_path('ihyaussunna-root/index.php');
        $this->assertFileExists($rootIndexPath);

        $content = file_get_contents($rootIndexPath);

        // Ensure institutional branding is present
        $this->assertStringContainsString('JAMIA IHYA-US-SUNNA', $content);
        $this->assertStringContainsString('Markazu Saquafathi Sunniyya', $content);
        $this->assertStringContainsString('COMING SOON', $content);

        // Ensure foodcounter routing logic is isolated
        $this->assertStringContainsString("str_starts_with(\$trimmedUri, 'foodcounter/')", $content);
        $this->assertStringContainsString('foodcounter/admin.php', $content);
        $this->assertStringContainsString('foodcounter/index.php', $content);
    }

    public function test_foodcounter_htaccess_and_admin_routing_rules_exist(): void
    {
        $htaccessPath = base_path('foodcounter/.htaccess');
        $this->assertFileExists($htaccessPath);

        $content = file_get_contents($htaccessPath);
        $this->assertStringContainsString('RewriteRule ^admin/?$ admin.php [L,QSA]', $content);
        $this->assertStringContainsString('RewriteRule ^api/?$ api.php [L,QSA]', $content);

        $indexPath = base_path('foodcounter/index.php');
        $this->assertFileExists($indexPath);
        $indexContent = file_get_contents($indexPath);
        $this->assertStringContainsString("preg_match('#/foodcounter/admin/?$#i', \$requestUri)", $indexContent);
    }

    public function test_root_htaccess_and_root_files_exist(): void
    {
        $rootHtaccess = base_path('ihyaussunna-root/.htaccess');
        $this->assertFileExists($rootHtaccess);

        $rootReadme = base_path('ihyaussunna-root/README.md');
        $this->assertFileExists($rootReadme);
    }
}
