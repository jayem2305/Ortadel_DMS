<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Password Reset - Ortadel DMS</title>
    <!--[if mso]>
    <style type="text/css">
        body, table, td {font-family: Arial, Helvetica, sans-serif !important;}
    </style>
    <![endif]-->
    <style>
        /* Reset Styles */
        body {
            margin: 0 !important;
            padding: 0 !important;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Helvetica', 'Arial', sans-serif;
            background-color: #f3f4f6;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        
        table {
            border-collapse: collapse;
            border-spacing: 0;
        }
        
        img {
            border: 0;
            height: auto;
            line-height: 100%;
            outline: none;
            text-decoration: none;
            -ms-interpolation-mode: bicubic;
        }

        /* Main Container */
        .email-wrapper {
            width: 100%;
            background-color: #f3f4f6;
            padding: 40px 20px;
        }
        
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        /* Header Styles */
        .header {
            background: linear-gradient(135deg, #2563EB 0%, #1E3A8A 100%);
            padding: 50px 30px;
            text-align: center;
            position: relative;
        }
        
        .header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><defs><pattern id="grid" width="20" height="20" patternUnits="userSpaceOnUse"><circle cx="10" cy="10" r="1" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grid)"/></svg>');
            opacity: 0.3;
        }
        
        .logo-container {
            position: relative;
            z-index: 1;
            background-color: rgba(255, 255, 255, 0.95);
            padding: 20px;
            border-radius: 12px;
            display: inline-block;
            margin-bottom: 20px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        
        .logo {
            max-width: 160px;
            height: auto;
            display: block;
        }
        
        .header-title {
            color: #ffffff;
            font-size: 28px;
            font-weight: 700;
            margin: 20px 0 0 0;
            position: relative;
            z-index: 1;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
            letter-spacing: -0.5px;
        }

        /* Content Styles */
        .content {
            padding: 45px 40px;
        }
        
        .greeting {
            color: #111827;
            font-size: 24px;
            font-weight: 700;
            margin: 0 0 25px 0;
            line-height: 1.3;
        }
        
        .message {
            color: #4b5563;
            font-size: 16px;
            line-height: 1.7;
            margin: 0 0 20px 0;
        }
        
        .message-highlight {
            color: #1f2937;
            font-weight: 600;
        }

        /* Button Styles */
        .button-container {
            text-align: center;
            margin: 40px 0;
        }
        
        .reset-button {
            display: inline-block;
            background: linear-gradient(135deg, #2563EB 0%, #1E3A8A 100%);
            color: #ffffff !important;
            text-decoration: none !important;
            padding: 18px 50px;
            border-radius: 10px;
            font-size: 17px;
            font-weight: 700;
            box-shadow: 0 8px 20px rgba(37, 99, 235, 0.35);
            letter-spacing: 0.3px;
            transition: all 0.3s ease;
        }
        
        .reset-button:hover {
            background: linear-gradient(135deg, #1E3A8A 0%, #1E40AF 100%);
            box-shadow: 0 12px 28px rgba(37, 99, 235, 0.45);
            transform: translateY(-2px);
        }

        /* Notice Boxes */
        .notice-box {
            border-radius: 10px;
            padding: 18px 22px;
            margin: 28px 0;
            border-left: 5px solid;
            backdrop-filter: blur(10px);
        }
        
        .expiry-notice {
            background: linear-gradient(135deg, #FEF3C7 0%, #FDE68A 100%);
            border-left-color: #F59E0B;
            box-shadow: 0 2px 8px rgba(245, 158, 11, 0.15);
        }
        
        .expiry-notice p {
            color: #78350F;
            font-size: 15px;
            margin: 0;
            line-height: 1.6;
        }
        
        .security-notice {
            background: linear-gradient(135deg, #FEE2E2 0%, #FECACA 100%);
            border-left-color: #DC2626;
            box-shadow: 0 2px 8px rgba(220, 38, 38, 0.15);
        }
        
        .security-notice p {
            color: #7F1D1D;
            font-size: 15px;
            margin: 0;
            line-height: 1.6;
        }
        
        .icon-emoji {
            font-size: 18px;
            margin-right: 8px;
            vertical-align: middle;
        }

        /* Divider */
        .divider {
            height: 2px;
            background: linear-gradient(90deg, transparent, #e5e7eb 20%, #e5e7eb 80%, transparent);
            margin: 35px 0;
        }

        /* Alternative Link */
        .alt-link-section {
            background-color: #f9fafb;
            border-radius: 10px;
            padding: 20px;
            margin: 25px 0;
            border: 1px solid #e5e7eb;
        }
        
        .alt-link-title {
            color: #6b7280;
            font-size: 13px;
            margin: 0 0 12px 0;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .link-text {
            color: #2563EB;
            font-size: 13px;
            word-break: break-all;
            margin: 0;
            padding: 12px;
            background-color: #ffffff;
            border-radius: 6px;
            border: 1px dashed #cbd5e1;
            font-family: 'Courier New', Courier, monospace;
        }

        /* Footer Styles */
        .footer {
            background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
            padding: 35px 30px;
            text-align: center;
            border-top: 2px solid #e5e7eb;
        }
        
        .footer-brand {
            margin-bottom: 20px;
        }
        
        .footer-title {
            color: #1f2937;
            font-size: 16px;
            font-weight: 700;
            margin: 0 0 8px 0;
        }
        
        .footer-subtitle {
            color: #6b7280;
            font-size: 14px;
            margin: 0 0 20px 0;
        }
        
        .footer-divider {
            height: 1px;
            background-color: #d1d5db;
            margin: 20px auto;
            max-width: 80%;
        }
        
        .footer-text {
            color: #9ca3af;
            font-size: 12px;
            margin: 8px 0;
            line-height: 1.5;
        }
        
        .footer-copyright {
            color: #6b7280;
            font-size: 13px;
            font-weight: 600;
            margin: 15px 0 8px 0;
        }
        
        .footer-disclaimer {
            color: #9ca3af;
            font-size: 11px;
            font-style: italic;
            margin-top: 15px;
        }

        /* Responsive Design */
        @media only screen and (max-width: 600px) {
            .email-wrapper {
                padding: 20px 10px !important;
            }
            
            .content {
                padding: 30px 25px !important;
            }
            
            .header {
                padding: 35px 20px !important;
            }
            
            .greeting {
                font-size: 22px !important;
            }
            
            .reset-button {
                padding: 16px 40px !important;
                font-size: 16px !important;
            }
            
            .logo {
                max-width: 140px !important;
            }
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
            <tr>
                <td align="center">
                    <div class="email-container">
                        <!-- Header with Logo -->
                        <div class="header">
                            <div class="logo-container">
                                <img src="https://ortadeltech.com/assets/images/ORTADEL_logo.png" alt="Ortadel Logo" class="logo">
                            </div>
                            <h1 class="header-title">🔐 Password Reset Request</h1>
                        </div>

                        <!-- Content -->
                        <div class="content">
                            <h2 class="greeting">Hello, {{ $userName }}! 👋</h2>
                            
                            <p class="message">
                                We received a request to <span class="message-highlight">reset your password</span> for your Ortadel Document Management System account.
                            </p>

                            <p class="message">
                                If you made this request, click the button below to choose a new password. If you didn't request this, you can safely ignore this email.
                            </p>

                            <!-- Reset Button -->
                            <div class="button-container">
                                <a href="{{ $url }}" class="reset-button">🔑 Reset My Password</a>
                            </div>

                            <!-- Expiry Notice -->
                            <div class="notice-box expiry-notice">
                                <p>
                                    <span class="icon-emoji">⏰</span>
                                    <strong>Time Limit:</strong> This password reset link will expire in <strong>{{ $expiryMinutes }} minutes</strong>. Please complete the reset process before it expires.
                                </p>
                            </div>

                            <!-- Security Notice -->
                            <div class="notice-box security-notice">
                                <p>
                                    <span class="icon-emoji">🔒</span>
                                    <strong>Security Notice:</strong> If you didn't request a password reset, please ignore this email. Your password will remain unchanged and your account is secure.
                                </p>
                            </div>

                            <div class="divider"></div>

                            <!-- Alternative Link -->
                            <div class="alt-link-section">
                                <p class="alt-link-title">Button not working? Use this link instead:</p>
                                <p class="link-text">{{ $url }}</p>
                            </div>

                            <p class="message" style="font-size: 14px; color: #9ca3af; margin-top: 25px;">
                                For security reasons, we cannot reset your password for you. You must use the link above to create a new password.
                            </p>
                        </div>

                        <!-- Footer -->
                        <div class="footer">
                            <div class="footer-brand">
                                <p class="footer-title">Ortadel Document Management System</p>
                                <p class="footer-subtitle">Barangay Documentation Management System</p>
                            </div>
                            
                            <div class="footer-divider"></div>
                            
                            <p class="footer-copyright">
                                © {{ $currentYear }} Ortadel DMS • Version 01.01.01
                            </p>
                            
                            <p class="footer-text">
                                Powered by Ortadel Technologies
                            </p>
                            
                            <p class="footer-disclaimer">
                                This is an automated email. Please do not reply to this message.<br>
                                For support, please contact your system administrator.
                            </p>
                        </div>
                    </div>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
