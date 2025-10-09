📘 Project Technical Documentation

System Name: Document Management Dashboard
Version: 1.1.0
Author: jayem
Last Updated: October 10, 2025

🧭 Overview

The Archiving system is an all-in-one system for managing files, folders, users, and access controls. It provides comprehensive monitoring tools, activity tracking, and document organization features. The platform supports global quick actions, enabling users to create new content (documents, folders, users, groups, and roles) from anywhere within the application.

🏠 Dashboard Module
The Dashboard provides a centralized summary of system activity and storage usage.

Key Features:
Displays uploaded data and statistical summaries.Shows log activities, file/folder/user counts per month.Monitors total MB used with a 512GB system storage limit.Includes visual charts and metrics for trends and usage overview.

📂 Document Management Module
Handles all uploaded files and folders with flexible display and management options.

Features:

Folder View:
Displays Title, Description, and Date Created.Allows creation, renaming, and deletion of folders.

File View:
Displays Title, Status, Date, and File Size.Includes bulk actions such as:
-Download
-Move
-Delete

View Modes:
List View – compact tabular layout for quick browsing.
Grid View – visual display with icons and metadata.

👥 User List Module
Displays and manages all users in the system.

Features:
-View detailed user information.
-Edit and update user data.
-Delete users when necessary.

🔐 User Management Module (RBAC)
Implements Role-Based Access Control (RBAC) to define system access levels.

Features:
-Create and manage Roles, Permissions, and Groups.
-Assign permissions to roles and link them to users.
-Ensure secure access and operation boundaries per role.

🧩 Access Controls Module
Defines which users or roles have access to specific features or data.

Features:
-Assign users to roles.
-Manage permission hierarchies.
-Audit and log access-level changes.

🏷️ Tags Module
Facilitates categorization and keyword tagging for better document organization.

Features:
-Create, edit, and delete tags.
-Assign tags to files and folders.
-Enable search and filtering by tags or categories.

📅 Calendar Module
Visualizes file and folder creation over time.

Features:
-Display file and folder creation dates in a calendar view.
-Filter by month, week, or day.
-Clickable entries to view related document details.

🧾 Audit Logs Module
Maintains a complete record of system and user activity.

Features:
-Logs all user actions such as login, upload, delete, and updates.
-Includes timestamps, user info, and action details.
-Supports filtering by date, user, or activity type.

🗑️ Recycle Bin Module
Stores deleted and expired files/folders temporarily for recovery.

Features:
-Displays all deleted and expired data.
-Allows restoration or permanent deletion.
-Tracks deletion date and data category (file/folder).

⚙️ Settings Module
Central configuration panel for managing application preferences and user profiles.

Features:
-Profile Settings: Update user information and preferences.
-User Logs: Display personal activity history.
-Version Information: View system version, updates, and metadata.

🌐 Global Keys (Quick Actions)
The Global Keys feature provides universal shortcuts accessible from any page in the system.

Features:
Quickly add new items without leaving the current page. Supported global actions:
➕ Add Document
📁 Add Folder
👤 Add User
👥 Add Group
🛡️ Add Role

Available as a floating button or keyboard shortcut menu for faster workflow.
Ensures seamless creation of content across all modules.

🧱 System Architecture
Components:
Frontend: Vue.js / Tailwind
Backend: Laravel
Database: MySQL
Storage: Local 512GB (Temporary)
Authentication: Laravel Sanctum
Authorization: Role-Based Access Control (RBAC)
Encryption Data: AES-252

📊 Dashboard Metrics Summary
Metric Description
Total Files Count of all uploaded files
Total Folders
Number of created folders
Total Users Registered and active users
Storage Used
Total MB consumed (512GB max)
Logs
Recorded system activities
Recycle Bin Deleted and expired items count

🚀 Future Enhancements
Add AI-assisted document classification using content recognition.
Implement custom user storage quotas.
Introduce collaborative document editing.
Add notifications and reminders for storage and access events.
Apply OCR

End of Document
