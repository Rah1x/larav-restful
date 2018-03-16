#Creating Restful APIs using Laravel ~5.3

# Stack
- PHP 7
- Redis
- Laravel 5.* (directory structore is 5.3)

# Scenario
1. add User (just email, password, added_on)
2. Signin & Fetch User info

# Validations
1. Attempts Check
2. Password encryption (to play around ive created mine enc method, but you should use laravel's own encryption instead)
3. Fields validation

# Files to see
- config\app.php
- routes\api.php
- app\Http\Helpers\*
- app\Http\Controller\api_\userCOntroller.php
