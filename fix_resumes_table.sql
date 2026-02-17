-- Fix resumes table schema
USE smart_resume_job_finder;

-- Add missing updated_at column
ALTER TABLE resumes 
ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER extra_details;

-- Add portfolio column if missing
ALTER TABLE resumes 
ADD COLUMN portfolio VARCHAR(255) NULL AFTER website;
