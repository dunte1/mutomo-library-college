For the **OLLMCHS Library Flutter mobile app**, I recommend **two separate APKs** sharing the same Laravel backend:

1. **Student App**
2. **Lecturer App**

Each app should feel like a modern native Android application using Material 3 with bottom navigation, pull-to-refresh, offline caching, biometric login, push notifications, dark mode, and responsive layouts.

---

# 1. Student App

## Splash Screen

* Animated logo
* Version check
* Server connectivity check
* Token validation
* Auto login
* Maintenance mode detection

---

## Onboarding (4–5 Screens)

* Welcome
* Digital Library
* Borrow Books
* Study Anywhere
* Notifications

Buttons:

* Skip
* Next
* Get Started

---

## Authentication

### Login

* Email / Admission Number
* Password
* Remember Me
* Fingerprint Login
* Face Unlock
* Login
* Forgot Password

---

### Register

* Admission Number
* Student ID
* First Name
* Last Name
* Email
* Phone
* Password
* Confirm Password
* Terms

---

### Forgot Password

* Email
* OTP verification
* Reset password

---

### Two Factor Authentication

* OTP
* Authenticator App
* Recovery Codes
* Trusted Device

---

# Main App

Use Bottom Navigation.

Tabs:

* Home
* Library
* Loans
* Messages
* Profile

---

# Navigation Drawer

## Student Profile Card

* Photo
* Name
* Admission Number
* Department
* Program
* Membership Status

---

Drawer Items

Dashboard

My Library

Digital Library

Reading Assignments

Recommendations

Bookmarks

Downloads

Library Card

Reservations

Loan History

Messages

Notifications

Events

Announcements

Profile

Settings

Help

About

Logout

---

# Dashboard

Cards

Current Loans

Books Due Soon

Outstanding Fines

Unread Messages

Downloaded Books

Reading Progress

Recommendations

Quick Actions

Borrow Book

Scan QR

Digital Library

Library Card

Reserve Book

Recent Activity

Latest Announcements

Upcoming Events

Reading Assignment Due

---

# Book Catalog

Features

Search

Filter

Sort

Categories

Authors

Publishers

Subjects

Availability

New Arrivals

Popular

Recently Added

Book Cards

Cover

Title

Author

Category

Available Copies

Shelf Location

Reserve Button

Bookmark Button

Details Button

---

# Book Details

Cover

Gallery

Title

Authors

Publisher

ISBN

Edition

Language

Pages

Description

Subjects

Availability

Shelf

Reviews

Ratings

Recommendations

Actions

Borrow

Reserve

Bookmark

Share

Download Sample

Read Online

---

# Digital Library

Tabs

Books

Lecture Notes

Past Papers

Journals

Research Papers

Policies

Books

Cards include

Cover

Title

Type

File Size

Pages

Downloads

Read Button

Download Button

Bookmark

---

# PDF Reader

Toolbar

Search

Bookmarks

Page Jump

Zoom

Dark Mode

Reading Progress

Highlight

Notes

Share

Download

---

# Reading Assignments

Assignment Card

Course

Lecturer

Book

Pages

Due Date

Status

Progress

Submit

Completion Percentage

---

# Recommendations

AI Recommendations

Recently Viewed

Trending

Related Books

Department Recommendations

---

# My Loans

Tabs

Current

History

Renewed

Overdue

Card

Book Cover

Borrow Date

Due Date

Renew Button

Return Status

Fine

Days Remaining

---

# Reservations

Pending

Ready

Collected

Expired

Cancel Reservation

---

# Library Card

QR Code

Barcode

Member ID

Membership Type

Expiry

Download PDF

Share

Verify

---

# Downloads

Downloaded Books

Offline Books

Remove

Open

Storage Used

---

# Bookmarks

Reading Bookmarks

Favorite Books

Favorite Authors

---

# Messages

Inbox

Sent

Archive

Compose

Reply

Reply All

Forward

Attachments

Search

Unread Badge

---

# Notifications

Tabs

Unread

Read

System

Library

Finance

Events

Assignments

Push Notification History

---

# Events

Calendar

Upcoming

Registered

Details

Add to Calendar

Directions

---

# Announcements

Cards

Title

Category

Date

Attachment

Read

Share

---

# Profile

Photo

Name

Email

Phone

Department

Program

Admission Number

Membership Status

Library Statistics

Edit Profile

---

# Settings

Theme

Dark Mode

Language

Biometric Login

Two Factor Authentication

Notification Preferences

Download Quality

Offline Sync

Auto Downloads

Privacy

Security

About

App Version

---

# Help

FAQs

Contact Librarian

Support

Report Problem

Feedback

---

# About

App Version

Privacy Policy

Terms

Licenses

---

# Lecturer App

The Lecturer App includes everything in the Student App plus lecturer-specific capabilities.

---

## Dashboard

Cards

Assigned Courses

Reading Assignments

Students

Submissions

Unread Messages

Library Usage

Announcements

---

## Drawer

Dashboard

My Courses

Reading Assignments

Students

Digital Library

Recommendations

Research Materials

Messages

Announcements

Events

Reports

Profile

Settings

Help

Logout

---

# My Courses

Course Cards

Students

Assignments

Resources

Attendance Link

---

# Reading Assignments

Create Assignment

Edit Assignment

Delete Assignment

Schedule Assignment

Assign Books

Assign PDFs

Assign Chapters

Assign Pages

Assign Due Date

Notify Students

---

# Assignment Details

Book

Course

Pages

Instructions

Due Date

Attachments

Status

---

# Student Progress

List

Progress

Completion %

Reading Time

Submission Status

Late Status

---

# Assignment Analytics

Completion Rate

Average Reading

Most Read Books

Late Students

Charts

---

# Digital Library

Upload Teaching Notes

Upload PDFs

Upload Lecture Slides

Upload Research Papers

Manage Resources

Version History

---

# Research Repository

Research Papers

Publications

Journals

Downloads

---

# Recommendations

Recommended Books

Department Books

AI Suggested Reading

---

# Reports

Assignment Reports

Reading Reports

Download Reports

PDF

Excel

---

# Messages

Direct Students

Course Broadcast

Attachments

Reply

Forward

Templates

---

# Notifications

Assignment Notifications

Reading Alerts

Library Updates

System Notifications

---

# Profile

Academic Details

Department

Courses

Office Contact

Research Interests

---

# Settings

Everything in Student Settings plus:

Assignment Defaults

Notification Rules

Preferred File Formats

Default Course Settings

---

## Shared Features (Both Apps)

Both applications should include:

* Material 3 UI with premium design
* Responsive layouts for phones and tablets
* Bottom navigation
* Navigation drawer with profile header
* Pull-to-refresh
* Skeleton loading placeholders
* Empty states with illustrations
* Infinite scrolling and pagination
* Offline caching (Hive/SQLite)
* Background synchronization
* JWT/Sanctum token management
* Secure storage for credentials
* Fingerprint and Face Unlock
* Two-Factor Authentication (Authenticator App + Recovery Codes)
* Push notifications (Firebase Cloud Messaging)
* QR code scanner and generator
* PDF viewer with progress tracking
* Search and advanced filters
* Dark and light themes
* Accessibility support (dynamic text, screen readers)
* Crash reporting and analytics
* Network status indicators
* Comprehensive error handling and retry mechanisms
* App update checks
* Multi-language support (if enabled in the backend)
* Secure logout with token revocation
* Automatic session renewal and expiration handling

