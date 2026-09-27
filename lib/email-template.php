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
              <td style="padding:13px 0;border-bottom:1px solid #F0F2F6;font-family:\'Segoe UI\',Helvetica,Arial,sans-serif;font-size:12px;letter-spacing:.02em;color:#8494A8;width:32%;vertical-align:top;">' . h($label) . '</td>
              <td style="padding:13px 0 13px 20px;border-bottom:1px solid #F0F2F6;font-family:\'Segoe UI\',Helvetica,Arial,sans-serif;font-size:14.5px;line-height:1.5;color:#0A1F3D;vertical-align:top;">' . nl2br(h($value)) . '</td>
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
<body style="margin:0;padding:0;background:#FFFFFF;">
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#FFFFFF;">
    <tr>
      <td align="center" style="padding:40px 24px;">
        <table role="presentation" width="720" cellpadding="0" cellspacing="0" style="max-width:720px;width:100%;">
          <tr>
            <td style="border-top:3px solid #2E6FBF;padding:22px 0 0;">
              <p style="margin:0 0 10px;font-family:'Segoe UI',Helvetica,Arial,sans-serif;color:#2E6FBF;font-size:11px;font-weight:700;letter-spacing:.14em;text-transform:uppercase;">Africa Kidney Health Summit &middot; 2027</p>
              <h1 style="margin:0;font-family:'Segoe UI',Helvetica,Arial,sans-serif;color:#0A1F3D;font-size:20px;font-weight:700;line-height:1.4;">{$safeTitle}</h1>
            </td>
          </tr>
          <tr>
            <td style="padding:22px 0 4px;">
              <p style="margin:0;font-family:'Segoe UI',Helvetica,Arial,sans-serif;color:#8494A8;font-size:13.5px;line-height:1.6;">A new request was just submitted through the summit website.</p>
            </td>
          </tr>
          <tr>
            <td style="padding:18px 0 0;">
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
                {$rowsHtml}
              </table>
            </td>
          </tr>
          <tr>
            <td style="padding:22px 0 0;">
              <p style="margin:0;font-family:'Segoe UI',Helvetica,Arial,sans-serif;font-size:13.5px;line-height:1.6;color:#5D6E86;">
                Reply directly to this email to respond to <strong style="color:#0A1F3D;">{$safeEmail}</strong>.
              </p>
            </td>
          </tr>
          <tr>
            <td style="padding:32px 0 0;border-top:1px solid #F0F2F6;margin-top:24px;">
              <p style="margin:24px 0 0;font-family:'Segoe UI',Helvetica,Arial,sans-serif;color:#8494A8;font-size:11.5px;line-height:1.7;">
                Sent automatically from the request form at kidneyhealth.africa &middot; Africa Kidney Health Summit 2027
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
