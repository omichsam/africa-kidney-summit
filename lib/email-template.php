<?php
/**
 * Renders the HTML body for request-form notification emails. Kept table-based with inline
 * styles (no external CSS/fonts-as-requirement) since that's what survives across Gmail,
 * Outlook and mobile mail clients. Plus Jakarta Sans is loaded via <link>; everywhere that's
 * stripped it falls back to Segoe UI/Helvetica, which is why the stack lists both.
 *
 * Softer, more conversational pass over the compact layout: rounded corners instead of square
 * edges, gentle whitespace and a light zebra tint instead of hard rule lines, roomier line-
 * height, and warmer copy — while keeping the structural choices that already worked (real
 * logo, short fields grouped up to three per row, one-line reply/footer).
 *
 * Each field row is its own standalone table (rather than sharing one table's column grid with
 * rows of a different cell count) — that's what guarantees a solo full-width field (e.g. a long
 * "Additional information" answer) actually spans the row instead of being aligned to just the
 * first column's width, which is what happens if you give a lone <td> width:100% inside a table
 * whose other rows have three cells.
 */

$FONT = "'Plus Jakarta Sans','Segoe UI',Helvetica,Arial,sans-serif";
$LOGO_URL = 'https://kidneyhealth.africa/src/assets/images/logo.png';

function h($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

// Short values (e.g. a name, an email, a country) are grouped up to three-to-a-row so the
// reader doesn't scroll through a long single column for what's really a compact form.
// Anything longer, or containing a line break (free-text messages), gets its own full-width row.
function is_short_value($value) {
    return mb_strlen($value) <= 30 && strpos($value, "\n") === false;
}

function render_field_content($label, $value, $font, $wrap) {
    $whiteSpace = $wrap ? 'normal' : 'nowrap';
    return '<p style="margin:0;font-family:' . $font . ';font-size:13px;line-height:1.55;white-space:' . $whiteSpace . ';">'
        . '<span style="font-weight:600;color:#8A97AB;">' . h($label) . ':</span> '
        . '<span style="color:#132A4D;font-weight:500;">' . nl2br(h($value)) . '</span>'
        . '</p>';
}

function render_field_row($items, $font, $zebra) {
    $count = count($items);
    $width = floor(100 / $count);
    $bg = $zebra ? 'background:#F3F6FB;' : '';
    $cells = '';
    foreach ($items as $i => [$label, $value]) {
        $pad = $i === 0 ? '10px 10px 10px 16px' : ($i === $count - 1 ? '10px 16px 10px 10px' : '10px 10px');
        $cells .= '<td width="' . $width . '%" style="padding:' . $pad . ';vertical-align:top;' . $bg . '">' . render_field_content($label, $value, $font, $count === 1) . '</td>';
    }
    return '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;"><tr>' . $cells . '</tr></table>';
}

function render_request_email($title, array $rows, $submitterEmail) {
    global $FONT, $LOGO_URL;

    $rowsHtml = '';
    $buffer = [];
    $zebra = false;

    $flush = function () use (&$buffer, &$rowsHtml, &$zebra, $FONT) {
        if (empty($buffer)) return;
        $rowsHtml .= render_field_row($buffer, $FONT, $zebra);
        $zebra = !$zebra;
        $buffer = [];
    };

    foreach ($rows as [$label, $value]) {
        if (is_short_value($value)) {
            $buffer[] = [$label, $value];
            if (count($buffer) === 3) $flush();
        } else {
            $flush();
            $rowsHtml .= render_field_row([[$label, $value]], $FONT, $zebra);
            $zebra = !$zebra;
        }
    }
    $flush();

    $safeTitle = h($title);
    $safeEmail = h($submitterEmail);
    $submittedAt = date('d M Y, H:i');

    return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
<title>{$safeTitle}</title>
</head>
<body style="margin:0;padding:0;background:#EEF2F7;">
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#EEF2F7;">
    <tr>
      <td align="center" style="padding:28px 16px;">
        <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background:#FFFFFF;border:1px solid #E7ECF3;border-radius:18px;">

          <tr>
            <td style="padding:24px 28px 4px;">
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr>
                <td width="130"><img src="{$LOGO_URL}" alt="Africa Kidney Health Summit" height="32" style="display:block;height:32px;width:auto;"></td>
                <td align="right" style="vertical-align:middle;">
                  <p style="margin:0;font-family:{$FONT};color:#A9B4C4;font-size:9.5px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;">Summit Secretariat &middot; 2027</p>
                </td>
              </tr></table>
            </td>
          </tr>

          <tr>
            <td style="padding:18px 28px 4px;">
              <h1 style="margin:0;font-family:{$FONT};color:#132A4D;font-size:17px;font-weight:800;letter-spacing:-.01em;line-height:1.4;">{$safeTitle}</h1>
              <p style="margin:4px 0 0;font-family:{$FONT};font-weight:500;color:#8A97AB;font-size:12px;line-height:1.5;">A new request just came in through kidneyhealth.africa, on {$submittedAt}.</p>
            </td>
          </tr>

          <tr>
            <td style="padding:14px 28px 0;">
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#F8FAFC;border-radius:14px;border-collapse:separate;overflow:hidden;">
                <tr><td style="padding:6px;">
                  {$rowsHtml}
                </td></tr>
              </table>
            </td>
          </tr>

          <tr>
            <td style="padding:18px 28px 26px;">
              <p style="margin:0;font-family:{$FONT};font-weight:500;font-size:12.5px;line-height:1.6;color:#5D6E86;">
                Just reply to this email to get back to <strong style="color:#132A4D;">{$safeEmail}</strong> directly.
              </p>
              <p style="margin:10px 0 0;font-family:{$FONT};font-weight:500;font-size:10.5px;color:#B7C0CE;">Africa Kidney Health Summit 2027 &middot; kidneyhealth.africa</p>
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

// Requester-facing confirmation email — sent back to the person who submitted a form, distinct
// from render_request_email() above (which notifies the secretariat). Deliberately reuses the
// same chrome (logo, colours, fonts, rounded card) so the two read as one family of mail.
function render_confirmation_email($formTitle, $firstName) {
    global $FONT, $LOGO_URL;
    $safeTitle = h($formTitle);
    $safeName = h($firstName ?: 'there');

    return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
<title>Your {$safeTitle} was received</title>
</head>
<body style="margin:0;padding:0;background:#EEF2F7;">
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#EEF2F7;">
    <tr>
      <td align="center" style="padding:28px 16px;">
        <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background:#FFFFFF;border:1px solid #E7ECF3;border-radius:18px;">

          <tr>
            <td style="padding:24px 28px 4px;">
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr>
                <td width="130"><img src="{$LOGO_URL}" alt="Africa Kidney Health Summit" height="32" style="display:block;height:32px;width:auto;"></td>
                <td align="right" style="vertical-align:middle;">
                  <p style="margin:0;font-family:{$FONT};color:#A9B4C4;font-size:9.5px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;">Summit Secretariat &middot; 2027</p>
                </td>
              </tr></table>
            </td>
          </tr>

          <tr>
            <td style="padding:20px 28px 0;">
              <h1 style="margin:0;font-family:{$FONT};color:#132A4D;font-size:17px;font-weight:800;letter-spacing:-.01em;line-height:1.4;">Your request has been received</h1>
            </td>
          </tr>

          <tr>
            <td style="padding:16px 28px 0;">
              <p style="margin:0 0 14px;font-family:{$FONT};font-weight:500;color:#132A4D;font-size:14px;line-height:1.65;">Dear {$safeName},</p>
              <p style="margin:0 0 14px;font-family:{$FONT};font-weight:500;color:#132A4D;font-size:14px;line-height:1.65;">Thank you for your submission. Your <strong>{$safeTitle}</strong> has been successfully received by the Africa Kidney Health Summit Secretariat and is now <strong>pending review</strong>.</p>
              <p style="margin:0 0 14px;font-family:{$FONT};font-weight:500;color:#132A4D;font-size:14px;line-height:1.65;">We will contact you using the email address provided should further information be required.</p>
              <p style="margin:0;font-family:{$FONT};font-weight:500;color:#132A4D;font-size:14px;line-height:1.65;">Kind regards,<br><strong>Africa Kidney Health Summit Secretariat</strong></p>
            </td>
          </tr>

          <tr>
            <td style="padding:22px 28px 26px;">
              <p style="margin:0;font-family:{$FONT};font-weight:500;font-size:10.5px;color:#B7C0CE;">Africa Kidney Health Summit 2027 &middot; kidneyhealth.africa</p>
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
