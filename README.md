# SalonProject

## Status

Work in progress – this project is not finished yet.

## Description

SalonProject is a web application for managing a salon.  
Features include:

- User management
- Services and categories management
- Appointment booking and notifications
- Admin dashboard

## Installation

1. Clone the repository:
   ```bash
   git clone https://github.com/Chammon18/SalonProject.git
   ```

## Local configuration (recommended)

Do not commit `public/dp.php` because it contains database credentials. Instead:

1. Copy the example file:
   ```bash
   cp public/dp.example.php public/dp.php
   ```
2. Open `public/dp.php` and replace `CHANGE_ME` with your real DB username/password.

Notes:

- `public/dp.php` is ignored by git so it stays only on your machine.
- If you ever committed real credentials before, change those passwords immediately.

## 1.Always Pull Latest Code First

git pull origin main

## 2. Edit or Add Files

- check status => git status
- Add Files to Git => git add (if only one file update => git add

/rebook.php)

## 3. Commit Changes

git commit -m "Anything can write in this blacket"

## 4. Push to GitHub

git push origin main
