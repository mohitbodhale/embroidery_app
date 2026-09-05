<?php
require 'vendor/autoload.php';
require 'config/bootstrap.php';

use Cake\ORM\Locator\LocatorAwareTrait;
use Authentication\PasswordHasher\DefaultPasswordHasher;

class PasswordTest {
    use LocatorAwareTrait;

    public function testPassword() {
        // Try to get users to verify connection works
        $Users = $this->getTableLocator()->get('Users');
        
        try {
            echo "Testing database connection...\n";
            $allUsers = $Users->find('all')->all();
            echo "✓ Database connection successful (PostgreSQL)\n\n";
        } catch (Exception $e) {
            echo "✗ Database connection failed: " . $e->getMessage() . "\n";
            return;
        }
        
        // Get all users
        echo "Users in database:\n";
        if (count($allUsers) === 0) {
            echo "  No users found!\n";
            echo "\nCreating admin user...\n";
            $this->createAdminUser($Users);
            return;
        }
        
        foreach ($allUsers as $user) {
            echo "  - ID: {$user->id}, Name: {$user->name}, Email: {$user->email}, Role: {$user->role}\n";
        }
        
        // Get admin user
        $adminUser = $Users->find()
            ->where(['email' => 'admin@stitchcraft.com'])
            ->first();
        
        if (!$adminUser) {
            echo "\n✗ Admin user not found!\n";
            echo "Creating admin user...\n";
            $this->createAdminUser($Users);
            return;
        }
        
        echo "\n\nAdmin User Details:\n";
        echo "  ID: {$adminUser->id}\n";
        echo "  Email: {$adminUser->email}\n";
        echo "  Name: {$adminUser->name}\n";
        echo "  Role: {$adminUser->role}\n";
        echo "  Organization ID: {$adminUser->organization_id}\n";
        echo "  Password Hash Length: " . strlen($adminUser->password) . "\n";
        echo "  Password Hash Preview: " . substr($adminUser->password, 0, 40) . "...\n\n";
        
        $hasher = new DefaultPasswordHasher();
        
        // Test with correct password
        $testPassword = 'password123';
        echo "Testing password verification:\n";
        echo "  Test Password: '{$testPassword}'\n";
        
        if ($hasher->check($testPassword, $adminUser->password)) {
            echo "  ✓ Password verification SUCCESSFUL!\n";
            echo "  You can login with: admin@stitchcraft.com / password123\n";
        } else {
            echo "  ✗ Password verification FAILED\n";
            echo "  Updating password hash...\n";
            $adminUser->password = $hasher->hash($testPassword);
            if ($Users->save($adminUser)) {
                echo "  ✓ Password updated successfully\n";
                echo "  You can now login with: admin@stitchcraft.com / password123\n";
            } else {
                echo "  ✗ Failed to update password\n";
            }
        }
    }
    
    private function createAdminUser($Users) {
        $Organizations = $this->getTableLocator()->get('Organizations');
        
        // Get or create organization
        $org = $Organizations->find()->first();
        if (!$org) {
            echo "  Creating organization...\n";
            $newOrg = $Organizations->newEmptyEntity();
            $newOrg->name = 'Default Organization';
            $newOrg->email = 'info@default.com';
            if (!$Organizations->save($newOrg)) {
                echo "  ✗ Failed to create organization\n";
                return;
            }
            $org = $newOrg;
        }
        
        echo "  Using organization: {$org->name} (ID: {$org->id})\n";
        
        $hasher = new DefaultPasswordHasher();
        $adminUser = $Users->newEmptyEntity();
        $adminUser->name = 'Admin User';
        $adminUser->email = 'admin@stitchcraft.com';
        $adminUser->role = 'admin';
        $adminUser->organization_id = $org->id;
        $adminUser->password = $hasher->hash('password123');
        
        if ($Users->save($adminUser)) {
            echo "  ✓ Admin user created!\n";
            echo "  Email: {$adminUser->email}\n";
            echo "  Password: password123\n";
        } else {
            echo "  ✗ Failed to create admin user\n";
            print_r($adminUser->getErrors());
        }
    }
}

$test = new PasswordTest();
$test->testPassword();

