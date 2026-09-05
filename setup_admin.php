<?php
require 'vendor/autoload.php';
require 'config/bootstrap.php';

use Cake\ORM\Locator\LocatorAwareTrait;
use Authentication\PasswordHasher\DefaultPasswordHasher;

class UserSetup {
    use LocatorAwareTrait;

    public function setupAdminUser() {
        $Users = $this->getTableLocator()->get('Users');
        
        // Try to find existing admin user
        $adminUser = $Users->find()
            ->where(['email' => 'admin@stitchcraft.com'])
            ->first();
        
        if ($adminUser) {
            echo "Admin user found:\n";
            echo "  ID: " . $adminUser->id . "\n";
            echo "  Name: " . $adminUser->name . "\n";
            echo "  Email: " . $adminUser->email . "\n";
            echo "  Role: " . $adminUser->role . "\n";
            echo "  Organization ID: " . $adminUser->organization_id . "\n";
            echo "  Password Hash: " . (strlen($adminUser->password) > 0 ? "SET" : "NOT SET") . "\n\n";
        } else {
            echo "Admin user not found. Creating...\n";
            
            // Check if organization exists
            $Organizations = $this->getTableLocator()->get('Organizations');
            $org = $Organizations->find()->first();
            
            if (!$org) {
                echo "No organization found. Creating default organization...\n";
                $newOrg = $Organizations->newEmptyEntity();
                $newOrg->name = 'Default Organization';
                $newOrg->email = 'info@default.com';
                if ($Organizations->save($newOrg)) {
                    echo "Organization created with ID: " . $newOrg->id . "\n";
                    $orgId = $newOrg->id;
                } else {
                    echo "Failed to create organization\n";
                    return;
                }
            } else {
                $orgId = $org->id;
                echo "Using existing organization ID: " . $orgId . "\n";
            }
            
            $adminUser = $Users->newEmptyEntity();
            $adminUser->name = 'Admin User';
            $adminUser->email = 'admin@stitchcraft.com';
            $adminUser->role = 'admin';
            $adminUser->organization_id = $orgId;
            
            $hasher = new DefaultPasswordHasher();
            $adminUser->password = $hasher->hash('password123');
            
            if ($Users->save($adminUser)) {
                echo "Admin user created successfully!\n";
                echo "  ID: " . $adminUser->id . "\n";
                echo "  Email: " . $adminUser->email . "\n";
                echo "  Password: password123\n";
            } else {
                echo "Failed to create admin user\n";
                print_r($adminUser->getErrors());
            }
        }
        
        // List all users
        echo "\n\nAll users in database:\n";
        $allUsers = $Users->find('all')->all();
        foreach ($allUsers as $user) {
            echo "- {$user->name} ({$user->email}) - Role: {$user->role}\n";
        }
    }
}

$setup = new UserSetup();
$setup->setupAdminUser();
