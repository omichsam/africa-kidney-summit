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
    foreach ($rows as [$label, $value]) {
        $rowsHtml .= '
            <tr>
              <td style="padding:11px 0;border-bottom:1px solid #EEF1F6;font-family:\'Segoe UI\',Helvetica,Arial,sans-serif;font-size:11px;font-weight:700;letter-spacing:.04em;color:#8494A8;text-transform:uppercase;width:36%;vertical-align:top;">' . h($label) . '</td>
              <td style="padding:11px 0 11px 18px;border-bottom:1px solid #EEF1F6;font-family:\'Segoe UI\',Helvetica,Arial,sans-serif;font-size:14px;line-height:1.5;color:#0A1F3D;vertical-align:top;">' . nl2br(h($value)) . '</td>
            </tr>';
    }

    $safeTitle = h($title);
    $safeEmail = h($submitterEmail);

    return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{$safeTitle}</title>
</head>
<body style="margin:0;padding:0;background:#F2F5FA;">
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#F2F5FA;">
    <tr>
      <td align="center" style="padding:32px 16px;">
        <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background:#FFFFFF;border-radius:14px;overflow:hidden;">
          <tr>
            <td style="background:#1B3A8F;background:linear-gradient(135deg,#1B3A8F,#2E6FBF);padding:30px 32px;">
              <p style="margin:0 0 6px;font-family:'Segoe UI',Helvetica,Arial,sans-serif;color:rgba(255,255,255,.72);font-size:11px;font-weight:700;letter-spacing:.16em;text-transform:uppercase;">Africa Kidney Health Summit &middot; 2027</p>
              <h1 style="margin:0;font-family:'Segoe UI',Helvetica,Arial,sans-serif;color:#FFFFFF;font-size:21px;font-weight:800;line-height:1.35;">{$safeTitle}</h1>
            </td>
          </tr>
          <tr>
            <td style="padding:28px 32px 6px;">
              <p style="margin:0 0 22px;font-family:'Segoe UI',Helvetica,Arial,sans-serif;color:#5D6E86;font-size:14px;line-height:1.6;">A new request was just submitted through the summit website. Details are below.</p>
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
                {$rowsHtml}
              </table>
            </td>
          </tr>
          <tr>
            <td style="padding:10px 32px 28px;">
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#F7F9FC;border-radius:10px;">
                <tr>
                  <td style="padding:14px 18px;font-family:'Segoe UI',Helvetica,Arial,sans-serif;font-size:13px;line-height:1.6;color:#0A1F3D;">
                    Reply directly to this email to respond to <strong>{$safeEmail}</strong> &mdash; it's already set as the reply-to address.
                  </td>
                </tr>
              </table>
            </td>
          </tr>
          <tr>
            <td style="padding:18px 32px 26px;border-top:1px solid #EEF1F6;">
              <p style="margin:0;font-family:'Segoe UI',Helvetica,Arial,sans-serif;color:#8494A8;font-size:11px;line-height:1.7;">
                Sent automatically from the request form at kidneyhealth.africa.<br>
                Africa Kidney Health Summit 2027
              </p>
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
