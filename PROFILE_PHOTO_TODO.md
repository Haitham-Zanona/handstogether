# Profile Photo Upload Implementation - Progress Tracker

## Tasks Completed:

-   [x] Database migration for profile_photo_path column
-   [x] Update ProfileUpdateRequest validation rules
-   [x] Modify ProfileController to handle photo uploads
-   [x] Update profile edit view with photo upload UI
-   [x] Configure storage for profile photos
-   [ ] Test functionality

## Detailed Steps:

### 1. Database Migration

-   Create migration: `php artisan make:migration add_profile_photo_path_to_users_table`
-   Add `profile_photo_path` column (string, nullable)
-   Run migration: `php artisan migrate`

### 2. ProfileUpdateRequest

-   Add validation for profile_photo: image, max:2048, mimes:jpg,jpeg,png

### 3. ProfileController

-   Handle file upload in update method
-   Store file in storage/app/public/profile-photos
-   Update user record with file path
-   Handle file deletion if photo is removed

### 4. Profile View

-   Add file input for photo upload
-   Display current profile photo
-   Add remove photo option
-   JavaScript for image preview

### 5. Storage Configuration

-   Ensure public disk is configured
-   Create symbolic link: `php artisan storage:link`

### 6. Testing

-   Test photo upload with valid/invalid files
-   Test photo removal
-   Verify file validation
-   Test with different user roles
