<?php
    require_once "includes/session_init.php";
    
    // If user is already logged in, redirect them
    if (isset($_SESSION['user_id'])) {
        header("Location: dashboard.php");
        die();
    }
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>WIL Connect | Register</title>
        <link rel="stylesheet" href="css/auth_pages.css">
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            
            body {
                font-family: 'Inter', sans-serif;
                color: #48557a;
                background: #eef3fb;
                line-height: 1.6;
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                margin: 0;
                padding: 20px;
            }

            .usertype-overlay {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(11, 31, 77, 0.45);
                backdrop-filter: blur(5px);
                -webkit-backdrop-filter: blur(5px);
                z-index: 1000;
                display: flex;
                justify-content: center;
                align-items: center;
            }

            .usertype-container {
                border-radius: 16px;
                box-shadow: 0 8px 32px rgba(0, 0, 0, 0.25);
                width: 520px;
                max-width: 92%;
                background: rgba(255, 255, 255, 0.98);
                border: 1px solid rgba(255, 255, 255, 0.5);
                animation: fadeIn 0.3s ease;
            }

            @keyframes fadeIn {
                from {
                    opacity: 0;
                    transform: scale(0.95);
                }
                to {
                    opacity: 1;
                    transform: scale(1);
                }
            }

            .usertype {
                padding: 32px 30px 28px;
                position: relative;
            }

            .goBack-link {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                text-decoration: none;
                color: #0b1f4d;
                font-size: 14px;
                font-weight: 500;
                margin-bottom: 18px;
                transition: color 0.2s ease;
            }

            .goBack-link:hover {
                color: #116ad8;
            }

            .goBack-link svg {
                vertical-align: middle;
            }

            #usertypeTitle {
                text-align: center;
                font-family: 'Poppins', sans-serif;
                font-size: 24px;
                color: #0b1f4d;
                margin: 8px 0 4px;
                font-weight: 600;
            }

            .role-opt {
                text-align: center;
                color: #6b7a9a;
                font-size: 14px;
                margin-bottom: 24px;
            }

            .role-choice {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 16px;
                margin: 20px 0 28px;
            }

            .role {
                display: block;
                text-decoration: none;
                text-align: left;
                background: #ffffff;
                border-left: 4px solid #116ad8;
                border-right: 4px solid #116ad8;
                border-radius: 12px;
                padding: 24px 20px 18px;
                transition: all 0.25s ease;
                box-shadow: 0 2px 8px rgba(17, 106, 216, 0.08);
            }

            .role:hover {
                border-color: #f5a623;
                box-shadow: 0 8px 24px rgba(245, 166, 35, 0.15);
                transform: translateY(-4px);
            }

            .role:hover .type {
                color: #f5a623;
            }
            
            .role:hover h3 {
                color: #f5a623;
            }

            .role h3 {
                font-family: 'Poppins', sans-serif;
                font-size: 17px;
                color: #116ad8;
                margin-bottom: 6px;
                transition: color 0.25s ease;
                font-weight: 600;
            }

            .role p {
                font-size: 13px;
                color: #48557a;
                line-height: 1.5;
                margin-bottom: 12px;
            }

            .type {
                font-size: 13px;
                font-weight: 600;
                color: #116ad8;
                transition: color 0.25s ease;
                display: inline-block;
            }

            .type::after {
                content: " →";
                transition: transform 0.25s ease;
                display: inline-block;
            }

            .role:hover .type::after {
                transform: translateX(4px);
            }

            .signUp {
                font-size: 13.5px;
                color: #48557a;
                text-align: center;
                margin-top: 8px;
            }

            .signUp a {
                color: #116ad8;
                font-weight: 600;
                text-decoration: none;
                transition: color 0.2s ease;
            }

            .signUp a:hover {
                color: #f5a623;
                text-decoration: underline;
            }

            @media (max-width: 580px) {
                .role-choice {
                    grid-template-columns: 1fr;
                    gap: 14px;
                }

                .usertype {
                    padding: 24px 18px 22px;
                }

                #usertypeTitle {
                    font-size: 20px;
                }

                .role {
                    padding: 18px 16px 14px;
                }

                .role h3 {
                    font-size: 15px;
                }
            }
        </style>
    </head>
    <body>
        <div class="usertype-overlay">
            <div class="usertype-container">
                <div class="usertype">
                    
                    <!-- Go Back Link -->
                    <a href="wil-home.php" class="goBack-link">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" width="16" height="16">
                            <path fill="#0b1f4d" d="M73.4 297.4C60.9 309.9 60.9 330.2 73.4 342.7L233.4 502.7C245.9 515.2 266.2 515.2 278.7 502.7C291.2 490.2 291.2 469.9 278.7 457.4L173.3 352L544 352C561.7 352 576 337.7 576 320C576 302.3 561.7 288 544 288L173.3 288L278.7 182.6C291.2 170.1 291.2 149.8 278.7 137.3C266.2 124.8 245.9 124.8 233.4 137.3L73.4 297.3z"/>
                        </svg>
                        Go Back
                    </a>

                    <!-- Heading -->
                    <h2 id="usertypeTitle">How would you like to sign up?</h2>
                    <p class="role-opt">Pick the option that fits you.</p>

                    <!-- Role Options -->
                    <div class="role-choice">

                        <!-- Recruiter -->
                        <a href="recruiter_register.php" class="role">
                            <h3>I'm a Recruiter</h3>
                            <p>Post opportunities and hire verified, career-ready students.</p>
                            <span class="type">Sign up as a Recruiter</span>
                        </a>

                        <!-- Student -->
                        <a href="student_register.php" class="role">
                            <h3>I'm a Student</h3>
                            <p>Find internships, earn verified badges, and launch your career.</p>
                            <span class="type">Sign up as a Student</span>
                        </a>

                    </div>

                    <!-- Login Link -->
                    <p class="signUp">
                        Already have an account? <a href="login.php">Log in</a>
                    </p>

                </div>
            </div>
        </div>
    </body>
</html>
