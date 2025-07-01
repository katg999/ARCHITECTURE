<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/Exception.php';
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';

$formMessage = '';
$formSuccess = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $fullName = isset($_POST['full-name']) ? htmlspecialchars(trim($_POST['full-name'])) : '';
    $senderEmail = isset($_POST['email']) ? filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL) : '';
    $phone = isset($_POST['phone']) ? htmlspecialchars(trim($_POST['phone'])) : '';
    $moveFrom = isset($_POST['move-from']) ? htmlspecialchars(trim($_POST['move-from'])) : '';
    $moveTo = isset($_POST['move-to']) ? htmlspecialchars(trim($_POST['move-to'])) : '';
    $movingDate = isset($_POST['moving-date']) ? htmlspecialchars(trim($_POST['moving-date'])) : '';
    $details = isset($_POST['details']) ? htmlspecialchars(trim($_POST['details'])) : '';

    if (empty($fullName) || !filter_var($senderEmail, FILTER_VALIDATE_EMAIL) || empty($phone) || empty($details)) {
        $formMessage = "Error: Please fill all required fields (Full Name, Email, Phone, Details) correctly.";
        $formSuccess = false;
    } else {
        $mailToCompany = new PHPMailer(true);
        $companyEmailSent = false;
        $companyEmailError = '';

        try {
            $mailToCompany->isSMTP();
            $mailToCompany->Host       = 'smtp.hostinger.com';
            $mailToCompany->SMTPAuth   = true;
            $mailToCompany->Username   = 'info@2blackvans.com.au';
            $mailToCompany->Password   = 'Abelbett@2025';
            $mailToCompany->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mailToCompany->Port       = 587;

            $mailToCompany->setFrom('info@2blackvans.com.au', '2 Black Vans Website Form');
            $mailToCompany->addAddress('info@2blackvans.com.au', '2 Black Vans Info');
            $mailToCompany->addAddress('abelbett3@gmail.com', 'Abel Bett');
            $mailToCompany->addReplyTo($senderEmail, $fullName);

            $mailToCompany->isHTML(true);
            $mailToCompany->Subject = 'New Move Inquiry from Website: ' . $fullName;

            $companyEmailBody = "<h2>New Moving Inquiry:</h2>";
            $companyEmailBody .= "<p><strong>Full Name:</strong> " . $fullName . "</p>";
            $companyEmailBody .= "<p><strong>Email:</strong> " . $senderEmail . "</p>";
            $companyEmailBody .= "<p><strong>Phone:</strong> " . $phone . "</p>";
            $companyEmailBody .= "<hr>";
            $companyEmailBody .= "<p><strong>Moving From:</strong> " . $moveFrom . "</p>";
            $companyEmailBody .= "<p><strong>Moving To:</strong> " . $moveTo . "</p>";
            $companyEmailBody .= "<p><strong>Preferred Moving Date:</strong> " . $movingDate . "</p>";
            $companyEmailBody .= "<hr>";
            $companyEmailBody .= "<h3>Details of Move:</h3><p>" . nl2br($details) . "</p>";

            $mailToCompany->Body    = $companyEmailBody;
            $mailToCompany->AltBody = strip_tags($companyEmailBody);

            $mailToCompany->send();
            $companyEmailSent = true;

        } catch (Exception $e) {
            $companyEmailSent = false;
            $companyEmailError = "Mailer Error (Company): {$mailToCompany->ErrorInfo}";
        }

        $confirmationEmailSent = false;
        $confirmationEmailError = '';
        if ($companyEmailSent) {
            $mailToSender = new PHPMailer(true);
            try {
                $mailToSender->isSMTP();
                $mailToSender->Host       = 'smtp.hostinger.com';
                $mailToSender->SMTPAuth   = true;
                $mailToSender->Username   = 'info@2blackvans.com.au';
                $mailToSender->Password   = 'Abelbett@2025';
                $mailToSender->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mailToSender->Port       = 587;

                $mailToSender->setFrom('info@2blackvans.com.au', '2 Black Vans Removals');
                $mailToSender->addAddress($senderEmail, $fullName);

                $mailToSender->isHTML(true);
                $mailToSender->Subject = 'Your Moving Inquiry with 2 Black Vans - Received';

                $confirmationEmailBody = "<h1>Dear " . $fullName . ",</h1>";
                $confirmationEmailBody .= "<p>Thank you for contacting 2 Black Vans Removals!</p>";
                $confirmationEmailBody .= "<p>We have successfully received your moving inquiry. Our team will review your details and get in touch with you as soon as possible.</p>";
                $confirmationEmailBody .= "<h3>Summary of Your Inquiry:</h3>";
                $confirmationEmailBody .= "<p><strong>Moving From:</strong> " . $moveFrom . "</p>";
                $confirmationEmailBody .= "<p><strong>Moving To:</strong> " . $moveTo . "</p>";
                $confirmationEmailBody .= "<p><strong>Preferred Date:</strong> " . $movingDate . "</p>";
                $confirmationEmailBody .= "<p><strong>Details:</strong> " . nl2br($details) . "</p>";
                $confirmationEmailBody .= "<hr><p>If you have any urgent questions, please don't hesitate to call us at +61 435 083 940.</p>";
                $confirmationEmailBody .= "<p>Best regards,<br>The 2 Black Vans Team</p>";

                $mailToSender->Body    = $confirmationEmailBody;
                $mailToSender->AltBody = strip_tags($confirmationEmailBody);

                $mailToSender->send();
                $confirmationEmailSent = true;

            } catch (Exception $e) {
                $confirmationEmailSent = false;
                $confirmationEmailError = "Mailer Error (Sender): {$mailToSender->ErrorInfo}";
            }
        }

        if ($companyEmailSent && $confirmationEmailSent) {
            $formMessage = "Thank You! Your inquiry has been sent successfully. We will contact you shortly. A confirmation email has also been sent to you.";
            $formSuccess = true;
        } elseif ($companyEmailSent && !$confirmationEmailSent) {
            $formMessage = "Thank You! Your inquiry has been sent to us. However, we couldn't send you a confirmation email at this moment. We will contact you shortly.";
            $formSuccess = true;
        } else {
            $formMessage = "Oops! Something went wrong. We encountered an error while sending your inquiry. Please try again later or contact us directly via phone or email.";
            $formSuccess = false;
        }
    }
} else {
    $formMessage = "Please submit the form from our contact page.";
    $formSuccess = false;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inquiry Status - 2 Black Vans Removals</title>
    <style>
        body { font-family: 'Inter', Arial, sans-serif; line-height: 1.6; padding: 20px; text-align: center; color: #333; background-color: #f4f4f4; }
        .container { max-width: 600px; margin: 40px auto; padding: 30px; border: 1px solid #ddd; border-radius: 8px; background-color: #fff; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .message-box h1 { font-size: 1.8em; margin-bottom: 15px; }
        .success-message { color: #155724; background-color: #d4edda; border:1px solid #c3e6cb; padding:15px; margin-top:20px; border-radius:5px;}
        .error-message { color: #721c24; background-color: #f8d7da; border:1px solid #f5c6cb; padding:15px; margin-top:20px; border-radius:5px;}
        a.button-link { display: inline-block; margin-top: 25px; padding: 10px 20px; background-color: #FF6600; color: white; text-decoration: none; border-radius: 5px; font-weight: bold; }
        a.button-link:hover { background-color: #E65C00; }
        hr { border: 0; border-top: 1px solid #eee; margin: 25px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="message-box">
            <?php if (!empty($formMessage)): ?>
                <h1>
                    <?php echo $formSuccess ? 'Message Sent!' : 'Submission Status'; ?>
                </h1>
                <p class="<?php echo $formSuccess ? 'success-message' : 'error-message'; ?>"><?php echo $formMessage; ?></p>
                <?php if ($formSuccess): ?>
                    <p>We will get back to you as soon as possible.</p>
                <?php else: ?>
                    <p>If the problem persists, please contact us via phone.</p>
                <?php endif; ?>
            <?php else: ?>
                <h1>Access Information</h1>
                <p>This page is for processing form submissions. Please use our contact form to send an inquiry.</p>
            <?php endif; ?>
        </div>
        <hr>
        <p><a href="contact.php" class="button-link">Return to Contact Page</a></p>    
        </div>
</body>
</html>