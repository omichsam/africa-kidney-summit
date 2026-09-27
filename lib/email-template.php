<?php
/**
 * Renders the HTML body for request-form notification emails. Kept table-based with inline
 * styles (no external CSS/fonts/images) since that's what survives across Gmail, Outlook and
 * mobile mail clients unmodified.
 */

function h($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function render_request_email($title, array $rows, $submitterEmail) {
    $rowsHtml = '';
    $zebra = false;
    foreach ($rows as [$label, $value]) {
        $bg = $zebra ? '#F7F9FC' : '#FFFFFF';
        $zebra = !$zebra;
        $rowsHtml .= '
            <tr>
              <td style="padding:12px 18px;border:1px solid #E5E9F0;background:' . $bg . ';font-family:Georgia,\'Times New Roman\',serif;font-size:11.5px;letter-spacing:.03em;color:#5D6E86;text-transform:uppercase;width:34%;vertical-align:top;">' . h($label) . '</td>
              <td style="padding:12px 18px;border:1px solid #E5E9F0;background:' . $bg . ';font-family:\'Segoe UI\',Helvetica,Arial,sans-serif;font-size:14px;line-height:1.5;color:#0A1F3D;vertical-align:top;">' . nl2br(h($value)) . '</td>
            </tr>';
    }

    $safeTitle = h($title);
    $safeEmail = h($submitterEmail);
    $refNumber = 'AKHS-2027-' . strtoupper(substr(sha1($title . $submitterEmail . microtime()), 0, 6));
    $submittedAt = date('d F Y \a\t H:i');

    return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{$safeTitle}</title>
</head>
<body style="margin:0;padding:0;background:#EEF1F6;">
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#EEF1F6;">
    <tr>
      <td align="center" style="padding:36px 20px;">
        <table role="presentation" width="680" cellpadding="0" cellspacing="0" style="max-width:680px;width:100%;background:#FFFFFF;border:1px solid #E5E9F0;">

          <tr>
            <td style="background:#0A1F3D;padding:26px 34px 20px;">
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                <tr>
                  <td style="font-family:Georgia,'Times New Roman',serif;color:#FFFFFF;font-size:16px;font-weight:700;letter-spacing:.03em;">Africa Kidney Health Summit</td>
                  <td align="right" style="font-family:'Segoe UI',Helvetica,Arial,sans-serif;color:rgba(255,255,255,.55);font-size:11px;letter-spacing:.04em;">Ref: {$refNumber}</td>
                </tr>
              </table>
              <p style="margin:4px 0 0;font-family:'Segoe UI',Helvetica,Arial,sans-serif;color:#8FA3C7;font-size:11px;font-weight:600;letter-spacing:.16em;text-transform:uppercase;">Official Secretariat Notification &middot; 2027</p>
            </td>
          </tr>
          <tr>
            <td style="background:#D32030;height:4px;line-height:4px;font-size:0;">&nbsp;</td>
          </tr>

          <tr>
            <td style="padding:32px 34px 6px;">
              <h1 style="margin:0 0 14px;font-family:Georgia,'Times New Roman',serif;color:#0A1F3D;font-size:20px;font-weight:700;line-height:1.4;">{$safeTitle}</h1>
              <p style="margin:0;font-family:'Segoe UI',Helvetica,Arial,sans-serif;color:#5D6E86;font-size:13.5px;line-height:1.65;">The following request has been formally submitted through the official Summit website and requires the Secretariat's attention.</p>
            </td>
          </tr>

          <tr>
            <td style="padding:22px 34px 0;">
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
                {$rowsHtml}
              </table>
            </td>
          </tr>

          <tr>
            <td style="padding:24px 34px 0;">
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-left:3px solid #2E6FBF;background:#F7F9FC;">
                <tr>
                  <td style="padding:14px 18px;font-family:'Segoe UI',Helvetica,Arial,sans-serif;font-size:13px;line-height:1.6;color:#0A1F3D;">
                    Please reply directly to this email to correspond with the requester at <strong>{$safeEmail}</strong>.
                  </td>
                </tr>
              </table>
            </td>
          </tr>

          <tr>
            <td style="padding:30px 34px 28px;">
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-top:1px solid #E5E9F0;">
                <tr>
                  <td style="padding-top:18px;font-family:'Segoe UI',Helvetica,Arial,sans-serif;color:#8494A8;font-size:11px;line-height:1.7;">
                    Submitted {$submittedAt} &middot; Automated notification from the official request system at kidneyhealth.africa<br>
                    Africa Kidney Health Summit 2027 &mdash; This message and any reply may contain information intended solely for the Secretariat.
                  </td>
                </tr>
              </table>
            </td>
          </tr>

        </table>
      </td>
    </tr>
  </table>
</body>
</html>
HTML;
}
