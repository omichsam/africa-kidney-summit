<?php
/**
 * Renders the HTML body for request-form notification emails. Kept table-based with inline
 * styles (no external CSS/fonts-as-requirement) since that's what survives across Gmail,
 * Outlook and mobile mail clients. Plus Jakarta Sans is loaded via <link>; everywhere that's
 * stripped it falls back to Segoe UI/Helvetica, which is why the stack lists both.
 *
 * Ultra-compact layout: the real logo replaces any colour band, title and submission time
 * share one line, short fields group up to three per row, and the reply note + footer collapse
 * into a single line — chosen after comparing it against several heavier alternatives.
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
    return '<p style="margin:0;font-family:' . $font . ';font-size:12.5px;line-height:1.4;white-space:' . $whiteSpace . ';">'
        . '<span style="font-weight:700;color:#8494A8;">' . h($label) . '</span>'
        . '<span style="color:#0A1F3D;font-weight:500;"> : ' . nl2br(h($value)) . '</span>'
        . '</p>';
}

function render_field_row($items, $font, $isFirstRow) {
    $count = count($items);
    $width = floor(100 / $count);
    $cells = '';
    foreach ($items as $i => [$label, $value]) {
        $borderRight = $i < $count - 1 ? 'border-right:1px solid #EEF1F6;' : '';
        $pad = $i === 0 ? '7px 12px 7px 0' : ($i === $count - 1 ? '7px 0 7px 12px' : '7px 12px');
        $cells .= '<td width="' . $width . '%" style="padding:' . $pad . ';vertical-align:top;' . $borderRight . '">' . render_field_content($label, $value, $font, $count === 1) . '</td>';
    }
    $top = $isFirstRow ? '' : 'border-top:1px solid #EEF1F6;';
    return '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;' . $top . '"><tr>' . $cells . '</tr></table>';
}

function render_request_email($title, array $rows, $submitterEmail) {
    global $FONT, $LOGO_URL;

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
<body style="margin:0;padding:0;background:#F2F5FA;">
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#F2F5FA;">
    <tr>
      <td align="center" style="padding:16px 16px;">
        <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background:#FFFFFF;border:1px solid #E9ECF3;">

          <tr>
            <td style="padding:12px 22px;border-bottom:1px solid #EEF1F6;">
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr>
                <td width="120"><img src="{$LOGO_URL}" alt="Africa Kidney Health Summit" height="28" style="display:block;height:28px;width:auto;"></td>
                <td align="right" style="vertical-align:middle;">
                  <p style="margin:0;font-family:{$FONT};color:#8FA3C7;font-size:9px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;">Secretariat Notification &middot; 2027</p>
                </td>
              </tr></table>
            </td>
          </tr>

          <tr>
            <td style="padding:12px 22px 2px;">
              <h1 style="margin:0;font-family:{$FONT};color:#0A1F3D;font-size:15.5px;font-weight:800;letter-spacing:-.01em;line-height:1.35;">{$safeTitle}</h1>
              <p style="margin:2px 0 0;font-family:{$FONT};font-weight:500;color:#8FA3C7;font-size:10.5px;">Submitted {$submittedAt} via kidneyhealth.africa</p>
            </td>
          </tr>

          <tr>
            <td style="padding:8px 22px 0;">
              {$rowsHtml}
            </td>
          </tr>

          <tr>
            <td style="padding:10px 22px 12px;border-top:1px solid #EEF1F6;">
              <p style="margin:10px 0 0;font-family:{$FONT};font-weight:500;font-size:10.5px;line-height:1.4;color:#5D6E86;">
                Reply to correspond with <strong style="color:#0A1F3D;">{$safeEmail}</strong> &middot; <span style="color:#B7C0CE;">Africa Kidney Health Summit 2027</span>
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
