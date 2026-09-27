<?php
/**
 * Renders the HTML body for request-form notification emails. Kept table-based with inline
 * styles (no external CSS/fonts-as-requirement/images) since that's what survives across
 * Gmail, Outlook and mobile mail clients. Plus Jakarta Sans is loaded two ways — a <link> and
 * a <style>@import — since different clients honour one but strip the other; everywhere both
 * are stripped it falls back to Segoe UI/Helvetica, which is why the stack lists both.
 *
 * Each field renders as "Label : Value" on a single line inside its own bordered box, with
 * padding on the surrounding table cell (not margin, which Outlook drops) creating the gap
 * between boxes — that's what keeps paired fields like Country code / Phone number visibly
 * separated instead of touching.
 */

$FONT = "'Plus Jakarta Sans','Segoe UI',Helvetica,Arial,sans-serif";

function h($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

// Short values (e.g. a name, an email, a country) are paired two-to-a-row so the reader
// doesn't have to scroll through a long single column for what's really a compact form.
// Anything longer, or containing a line break (free-text messages), gets its own full-width row.
// Applies identically regardless of which of the three request forms produced the rows.
function is_short_value($value) {
    return mb_strlen($value) <= 40 && strpos($value, "\n") === false;
}

function render_field_block($label, $value, $font) {
    return '<div style="border:1px solid #E5E9F0;border-radius:8px;padding:10px 14px;background:#FFFFFF;">'
        . '<p style="margin:0;font-family:' . $font . ';font-size:13.5px;line-height:1.5;">'
        . '<span style="font-weight:700;color:#8494A8;letter-spacing:.02em;">' . h($label) . '</span>'
        . '<span style="color:#0A1F3D;font-weight:500;"> : ' . nl2br(h($value)) . '</span>'
        . '</p></div>';
}

function render_request_email($title, array $rows, $submitterEmail) {
    global $FONT;

    $rowsHtml = '';
    $pending = null;

    foreach ($rows as [$label, $value]) {
        if (is_short_value($value)) {
            if ($pending === null) {
                $pending = [$label, $value];
                continue;
            }
            $rowsHtml .= '<tr>'
                . '<td width="50%" style="padding:5px 5px 5px 0;vertical-align:top;">' . render_field_block($pending[0], $pending[1], $FONT) . '</td>'
                . '<td width="50%" style="padding:5px 0 5px 5px;vertical-align:top;">' . render_field_block($label, $value, $FONT) . '</td>'
                . '</tr>';
            $pending = null;
        } else {
            if ($pending !== null) {
                $rowsHtml .= '<tr><td colspan="2" style="padding:5px 0;">' . render_field_block($pending[0], $pending[1], $FONT) . '</td></tr>';
                $pending = null;
            }
            $rowsHtml .= '<tr><td colspan="2" style="padding:5px 0;">' . render_field_block($label, $value, $FONT) . '</td></tr>';
        }
    }
    if ($pending !== null) {
        $rowsHtml .= '<tr><td colspan="2" style="padding:5px 0;">' . render_field_block($pending[0], $pending[1], $FONT) . '</td></tr>';
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
<style>
  @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap');
  body, table, td, p, a, span, h1 { -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale; text-rendering: optimizeLegibility; }
</style>
<title>{$safeTitle}</title>
</head>
<body style="margin:0;padding:0;background:#EEF1F6;">
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#EEF1F6;">
    <tr>
      <td align="center" style="padding:24px 16px;">
        <table role="presentation" width="680" cellpadding="0" cellspacing="0" style="max-width:680px;width:100%;background:#FFFFFF;border:1px solid #E5E9F0;">

          <tr>
            <td style="background:#0A1F3D;padding:18px 30px;">
              <p style="margin:0 0 3px;font-family:{$FONT};color:#FFFFFF;font-size:16px;font-weight:800;letter-spacing:-.01em;">Africa Kidney Health Summit</p>
              <p style="margin:0;font-family:{$FONT};color:#8FA3C7;font-size:10.5px;font-weight:700;letter-spacing:.14em;text-transform:uppercase;">Official Secretariat Notification &middot; 2027</p>
            </td>
          </tr>
          <tr>
            <td style="background:#D32030;height:3px;line-height:3px;font-size:0;">&nbsp;</td>
          </tr>

          <tr>
            <td style="padding:20px 30px 4px;">
              <h1 style="margin:0 0 6px;font-family:{$FONT};color:#0A1F3D;font-size:18px;font-weight:800;letter-spacing:-.02em;line-height:1.35;">{$safeTitle}</h1>
              <p style="margin:0;font-family:{$FONT};font-weight:500;color:#5D6E86;font-size:12.5px;line-height:1.5;">Formally submitted through the official Summit website. Requires the Secretariat's attention.</p>
            </td>
          </tr>

          <tr>
            <td style="padding:12px 26px 0;">
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
                {$rowsHtml}
              </table>
            </td>
          </tr>

          <tr>
            <td style="padding:14px 30px 0;">
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-left:3px solid #2E6FBF;background:#F7F9FC;">
                <tr>
                  <td style="padding:10px 16px;font-family:{$FONT};font-weight:500;font-size:12.5px;line-height:1.5;color:#0A1F3D;">
                    Reply directly to this email to correspond with the requester at <strong>{$safeEmail}</strong>.
                  </td>
                </tr>
              </table>
            </td>
          </tr>

          <tr>
            <td style="padding:18px 30px 20px;">
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-top:1px solid #E5E9F0;">
                <tr>
                  <td style="padding-top:12px;font-family:{$FONT};font-weight:500;color:#8494A8;font-size:10.5px;line-height:1.55;">
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
