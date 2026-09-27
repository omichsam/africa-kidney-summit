<?php
/**
 * Renders the HTML body for request-form notification emails. Kept table-based with inline
 * styles (no external CSS/fonts-as-requirement/images) since that's what survives across
 * Gmail, Outlook and mobile mail clients — the Google Fonts <link> is a progressive
 * enhancement that several clients (Gmail app, Apple Mail, Outlook.com) do honour; everywhere
 * else it falls back to the closest system font.
 *
 * Design: a rounded notification card (badge + eyebrow header, no full-bleed colour band),
 * fields grouped inside one shaded panel rather than individual boxes, and a real red CTA
 * button to reply — distinct from the letterhead-style layout used previously.
 */

$FONT = "'Plus Jakarta Sans','Segoe UI',Helvetica,Arial,sans-serif";

function h($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

// Short values (e.g. a name, an email, a country) are paired two-to-a-row so the reader
// doesn't have to scroll through a long single column for what's really a compact form.
// Anything longer, or containing a line break (free-text messages), gets its own full-width row.
function is_short_value($value) {
    return mb_strlen($value) <= 40 && strpos($value, "\n") === false;
}

function render_field_content($label, $value, $font) {
    return '<p style="margin:0 0 2px;font-family:' . $font . ';font-size:10.5px;font-weight:700;letter-spacing:.06em;color:#98A6BC;text-transform:uppercase;">' . h($label) . '</p>'
        . '<p style="margin:0;font-family:' . $font . ';font-size:14px;font-weight:600;line-height:1.45;color:#132A4D;">' . nl2br(h($value)) . '</p>';
}

function render_request_email($title, array $rows, $submitterEmail) {
    global $FONT;

    $rowsHtml = '';
    $pending = null;
    $isFirstRow = true;
    $topBorder = 'border-top:1px solid #E5E9F0;';

    foreach ($rows as [$label, $value]) {
        if (is_short_value($value)) {
            if ($pending === null) {
                $pending = [$label, $value];
                continue;
            }
            $border = $isFirstRow ? '' : $topBorder;
            $rowsHtml .= '<tr>'
                . '<td width="50%" style="padding:12px 18px;vertical-align:top;border-right:1px solid #E5E9F0;' . $border . '">' . render_field_content($pending[0], $pending[1], $FONT) . '</td>'
                . '<td width="50%" style="padding:12px 18px;vertical-align:top;' . $border . '">' . render_field_content($label, $value, $FONT) . '</td>'
                . '</tr>';
            $pending = null;
            $isFirstRow = false;
        } else {
            if ($pending !== null) {
                $border = $isFirstRow ? '' : $topBorder;
                $rowsHtml .= '<tr><td colspan="2" style="padding:12px 18px;' . $border . '">' . render_field_content($pending[0], $pending[1], $FONT) . '</td></tr>';
                $pending = null;
                $isFirstRow = false;
            }
            $border = $isFirstRow ? '' : $topBorder;
            $rowsHtml .= '<tr><td colspan="2" style="padding:12px 18px;' . $border . '">' . render_field_content($label, $value, $FONT) . '</td></tr>';
            $isFirstRow = false;
        }
    }
    if ($pending !== null) {
        $border = $isFirstRow ? '' : $topBorder;
        $rowsHtml .= '<tr><td colspan="2" style="padding:12px 18px;' . $border . '">' . render_field_content($pending[0], $pending[1], $FONT) . '</td></tr>';
    }

    $safeTitle = h($title);
    $safeEmail = h($submitterEmail);
    $submittedAt = date('d F Y \a\t H:i');

    return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
<title>{$safeTitle}</title>
</head>
<body style="margin:0;padding:0;background:#F1F3F8;">
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#F1F3F8;">
    <tr>
      <td align="center" style="padding:32px 16px;">
        <table role="presentation" width="640" cellpadding="0" cellspacing="0" style="max-width:640px;width:100%;background:#FFFFFF;border:1px solid #E9ECF3;border-radius:16px;">

          <tr>
            <td style="padding:24px 28px 18px;">
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                <tr>
                  <td width="42" style="vertical-align:middle;">
                    <table role="presentation" width="40" height="40" cellpadding="0" cellspacing="0" style="width:40px;height:40px;background:#1B3A8F;border-radius:11px;">
                      <tr><td align="center" valign="middle" style="font-family:{$FONT};color:#FFFFFF;font-size:13px;font-weight:800;">AK</td></tr>
                    </table>
                  </td>
                  <td style="vertical-align:middle;padding-left:12px;">
                    <p style="margin:0;font-family:{$FONT};color:#132A4D;font-size:14.5px;font-weight:800;">Africa Kidney Health Summit</p>
                    <p style="margin:1px 0 0;font-family:{$FONT};color:#98A6BC;font-size:10.5px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;">2027 &middot; Secretariat</p>
                  </td>
                  <td align="right" style="vertical-align:middle;">
                    <span style="display:inline-block;font-family:{$FONT};background:#FDECEE;color:#D32030;font-size:10px;font-weight:800;letter-spacing:.06em;text-transform:uppercase;padding:6px 12px;border-radius:20px;">New request</span>
                  </td>
                </tr>
              </table>
            </td>
          </tr>

          <tr><td style="border-top:1px solid #EEF1F6;"></td></tr>

          <tr>
            <td style="padding:24px 28px 6px;">
              <h1 style="margin:0 0 6px;font-family:{$FONT};color:#132A4D;font-size:19px;font-weight:800;letter-spacing:-.02em;line-height:1.35;">{$safeTitle}</h1>
              <p style="margin:0;font-family:{$FONT};font-weight:500;color:#6B7A93;font-size:12.5px;line-height:1.5;">Submitted through the official Summit website and awaiting Secretariat review.</p>
            </td>
          </tr>

          <tr>
            <td style="padding:14px 28px 0;">
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#F7F9FC;border-radius:12px;border-collapse:separate;overflow:hidden;">
                {$rowsHtml}
              </table>
            </td>
          </tr>

          <tr>
            <td align="center" style="padding:22px 28px 6px;">
              <table role="presentation" cellpadding="0" cellspacing="0">
                <tr>
                  <td style="background:#D32030;border-radius:10px;">
                    <a href="mailto:{$safeEmail}" style="display:inline-block;padding:12px 26px;font-family:{$FONT};color:#FFFFFF;font-size:13.5px;font-weight:700;text-decoration:none;">Reply to {$safeEmail}</a>
                  </td>
                </tr>
              </table>
            </td>
          </tr>

          <tr>
            <td style="padding:20px 28px 24px;">
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-top:1px solid #EEF1F6;">
                <tr>
                  <td align="center" style="padding-top:14px;font-family:{$FONT};font-weight:500;color:#98A6BC;font-size:10.5px;line-height:1.55;">
                    Submitted {$submittedAt} &middot; kidneyhealth.africa<br>
                    Africa Kidney Health Summit 2027
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
