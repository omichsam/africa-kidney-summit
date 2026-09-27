<?php
/**
 * Renders the HTML body for request-form notification emails. Kept table-based with inline
 * styles (no external CSS/fonts-as-requirement/images) since that's what survives across
 * Gmail, Outlook and mobile mail clients. Plus Jakarta Sans is loaded two ways — a <link> and
 * a <style>@import — since different clients honour one but strip the other; everywhere both
 * are stripped it falls back to Segoe UI/Helvetica, which is why the stack lists both.
 *
 * Fields sit inside one tinted panel, grouped into rows of up to three short fields (icon +
 * "Label: value"), with any longer free-text field taking its own full-width row. Icons are
 * inline SVG chosen by keyword match on the label — they degrade gracefully (blank circle) on
 * clients that strip SVG, such as Outlook desktop.
 */

$FONT = "'Plus Jakarta Sans','Segoe UI',Helvetica,Arial,sans-serif";

$ICONS = [
    'user' => '<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
    'mail' => '<path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/>',
    'phone' => '<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.362 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.338 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/>',
    'globe' => '<circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>',
    'briefcase' => '<rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>',
    'file' => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14,2 14,8 20,8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/>',
    'tag' => '<path d="M20.59 13.41 11 3.83A2 2 0 0 0 9.59 3.24L3 3v6.59a2 2 0 0 0 .59 1.41l9.58 9.58a2 2 0 0 0 2.82 0l6.6-6.6a2 2 0 0 0 0-2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/>',
];

function h($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

// Short values (e.g. a name, an email, a country) are grouped up to three-to-a-row so the
// reader doesn't scroll through a long single column for what's really a compact form.
// Anything longer, or containing a line break (free-text messages), gets its own full-width row.
function is_short_value($value) {
    return mb_strlen($value) <= 34 && strpos($value, "\n") === false;
}

function field_icon_key($label) {
    $l = mb_strtolower($label);
    if (strpos($l, 'email') !== false) return 'mail';
    if (strpos($l, 'phone') !== false) return 'phone';
    if (strpos($l, 'country') !== false) return 'globe';
    if (strpos($l, 'name') !== false || strpos($l, 'contact person') !== false) return 'user';
    if (strpos($l, 'organi') !== false || strpos($l, 'company') !== false) return 'briefcase';
    if (strpos($l, 'message') !== false || strpos($l, 'information') !== false || strpos($l, 'additional') !== false) return 'file';
    return 'tag';
}

function render_field_content($label, $value, $font, $wrap) {
    global $ICONS;
    $iconPath = $ICONS[field_icon_key($label)];
    $whiteSpace = $wrap ? 'normal' : 'nowrap';
    return '<table role="presentation" cellpadding="0" cellspacing="0" width="100%"><tr>'
        . '<td width="30" style="vertical-align:top;padding-top:1px;">'
        . '<table role="presentation" width="26" height="26" cellpadding="0" cellspacing="0" style="width:26px;height:26px;background:#0A1F3D;border-radius:50%;">'
        . '<tr><td align="center" valign="middle"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#FFFFFF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' . $iconPath . '</svg></td></tr>'
        . '</table></td>'
        . '<td style="vertical-align:middle;padding-left:4px;">'
        . '<p style="margin:0;font-family:' . $font . ';font-size:13px;line-height:1.5;white-space:' . $whiteSpace . ';">'
        . '<span style="color:#6B7A93;font-weight:500;">' . h($label) . ':</span> '
        . '<span style="color:#0A1F3D;font-weight:700;">' . nl2br(h($value)) . '</span>'
        . '</p></td>'
        . '</tr></table>';
}

// Renders one logical row as its own mini-table so the column count can vary (1, 2 or 3)
// without relying on colspan tricks. $items is a list of [label, value] pairs.
function render_field_row($items, $font, $isFirstRow) {
    $count = count($items);
    $width = floor(100 / $count);
    $cells = '';
    foreach ($items as $i => [$label, $value]) {
        $borderRight = $i < $count - 1 ? 'border-right:1px solid #E3E9F5;' : '';
        $cells .= '<td width="' . $width . '%" style="padding:14px 18px;vertical-align:top;' . $borderRight . '">' . render_field_content($label, $value, $font, $count === 1) . '</td>';
    }
    $borderTop = $isFirstRow ? '' : 'border-top:1px solid #E3E9F5;';
    return '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;' . $borderTop . '"><tr>' . $cells . '</tr></table>';
}

function render_request_email($title, array $rows, $submitterEmail) {
    global $FONT;

    $rowsHtml = '';
    $buffer = [];
    $isFirstRow = true;

    $flush = function () use (&$buffer, &$rowsHtml, &$isFirstRow, $FONT) {
        if (empty($buffer)) return;
        $rowsHtml .= render_field_row($buffer, $FONT, $isFirstRow);
        $isFirstRow = false;
        $buffer = [];
    };

    foreach ($rows as [$label, $value]) {
        if (is_short_value($value)) {
            $buffer[] = [$label, $value];
            if (count($buffer) === 3) $flush();
        } else {
            $flush();
            $rowsHtml .= render_field_row([[$label, $value]], $FONT, $isFirstRow);
            $isFirstRow = false;
        }
    }
    $flush();

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
            <td style="padding:16px 30px 0;">
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#F7FAFF;border:1px solid #E3E9F5;border-radius:14px;border-collapse:separate;overflow:hidden;">
                <tr><td style="padding:0;">
                  {$rowsHtml}
                </td></tr>
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
