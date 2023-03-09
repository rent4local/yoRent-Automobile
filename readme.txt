Release Number:  
Release Date: 

New Features: N/A

Updates: N/A

Fixes :
    #070767 - Manage Shop > General, Enable Free Shipping <label> tag is missing class="field_label" used on all other labels, making text big for that one label.

======================================================================================

Release Number: RV-3.0
Release Date: 22-October-2021

Installation steps:
    -   Download the files and configured with your development/production environment.
    -   You can get all the files mentioned in .gitignore file from git-ignored-files directory.
    -   Renamed -.htaccess file to .htaccess from {document root}, {document root}/public and {document root}/user-uploads/cropped directory
    -   Upload Fatbit library and licence files under {document root}/library.
    -   Define DB configuration under {document root}/public/settings.php
    -   Update basic configuration as per your system requirements under {document root}/conf directory.

Notes:
    
    Procedures: 
        Execute "{siteurl}/admin/admin-users/create-procedures" is mandatory.

    Composer:
        => Run command "composer update" at root of the project to update composer and fetch all dependent libraries: 

    s3 bucket notes for bulk media:
        => Create a Lambda function.
        => Add triggers and upload zip file from  git-ignored-files/user-uploads/lib-files/fatbit-s3-zip-extractor.zip
        => Set permission and update Resource based on function created by you.
        {
            "Version": "2012-10-17",
            "Statement": [
                {
                    "Effect": "Allow",
                    "Action": "logs:CreateLogGroup",
                    "Resource": "arn:aws:logs:us-east-2:765751105868:*"
                },
                {
                    "Effect": "Allow",
                    "Action": [
                        "logs:CreateLogStream",
                        "logs:PutLogEvents"
                    ],
                    "Resource": "arn:aws:logs:*:*:*"
                },
                {
                    "Effect": "Allow",
                    "Action": [
                        "s3:PutObject",
                        "s3:GetObject",
                        "s3:DeleteObject"
                    ],
                    "Resource": [
                        "*"
                    ]
                }
            ]
        }

    2Checkout Payment Gateway:
        To Test Sandbox Payment Refer This: https://knowledgecenter.2checkout.com/Documentation/09Test_ordering_system/01Test_payment_methods
