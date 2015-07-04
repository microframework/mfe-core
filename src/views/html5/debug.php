<?php namespace mfe\core\views\html5;

use mfe\core\libs\components\CDebug;

/**
 * Debug view.html5 file
 */
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset=utf-8>
    <title>Debug | {info}</title>
    <style> * {
            margin: 0;
            padding: 0;
            border: 0;
            outline: 0;
        }

        html, body {
            min-width: 100%;
            min-height: 100%;
            width: 100%;
            height: 100%;
            z-index: 0;
        }

        #error_page {
            position: relative;
            min-height: 100%;
        }

        .error_box {
            position: relative;
            min-height: 100%;
            padding-bottom: 23px;
        }

        .error_box .error_box_header, .error_box .error_box_body, .error_box .error_box_footer {
            position: relative;
            background: #fff;
            color: #333;
        }

        .error_box .error_box_header {
            position: relative;
            height: 10px;
        }

        .error_box .error_panel .header {
            position: relative;
            display: block;
            top: -10px;
            font-family: Arial, serif;
            font-size: 12pt;
            font-weight: bold;
            margin: 10px 0 8px 20px;
        }

        .error_panel {
            border-bottom: #ccc solid 1px;
        }

        .error_box .error_panel .notice {
            top: -10px;
            position: relative;
            display: block;
            margin: 5px 0 0 0;
            padding: 5px 30px;
            font-family: Arial, serif;
            font-weight: bold;
            font-size: 10pt;
            background: #F7F7F7;
            border-top: #ccc solid 1px;
            border-bottom: #ccc solid 1px;
        }

        .error_box .error_box_body {
            position: relative;
            padding: 5px 0;
        }

        .error_box .error_box_body > div.code,
        .error_box .error_box_body .trace {
            position: relative;
            display: block;
            font-family: Calibri, serif;
            font-style: normal;
            font-size: 0.81em;
            padding: 5px  5px;
            margin: 0 5px 0 20px;
        }

        .error_box_footer {
            position: fixed;
            background: #fff;
            left: 0;
            right: 0;
            bottom: 0;
            height: 23px;
            border-top: 1px solid #ccc;
            font-family: Calibri, serif;
            font-style: normal;
            font-size: 0.8em;
        }

        .error_box_footer .copy {
            position: absolute;
            bottom: 5px;
            right: 10px;
        }

        .error_box_footer .complete_time {
            position: absolute;
            bottom: 5px;
            left: 10px;
        }

        pre {
            font: normal 11pt Menlo, Consolas, "Lucida Console", Monospace;
        }

        pre span.error {
            display: block;
            background: #f3f3f3;
        }

        pre span.ln {
            color: #999;
            padding-right: 0.5em;
            border-right: 1px solid #ccc;
        }

        .code pre {
            background-color: #ffffff;
            margin: 0.5em 0;
            padding: 0.5em;
            line-height: 125%;
            border: 1px solid #eee;
        }
    </style>
</head>
<body>
<div id=error_page>
    <div class=error_box>
        <div class=error_box_header></div>
        <?php $count = 1 ?>
        <?php if ($this->errors): ?>
            <?php foreach ($this->errors as $error): ?>
                <div class=error_panel>
                    <div class=error_box_body>
                        <div>
                            <span class=header><?= $count++ ?>. [<?= $error[0] ?>]</span>
                        </div>
                        <div>
                            <span class=notice><?= $error[1] ?> in <?= $error[2] ?> on line <?= $error[3] ?></span>
                        </div>
                        <?= CDebug::renderSourceCode($error[2], $error[3], 15); ?>
                        <div class="trace">
                            <?php $countStack = 0 ?>
                            <?php foreach ($error[4] as $value): ?>
                                <?= $countStack++ ?>.
                                <strong> <?= $value['class'] . $value['type'] . $value['function'] ?>()</strong>
                            <?php if ($value['file']): ?> in <strong><?= $value['file'] ?></strong><?php endif ?>
                            <?php if ($value['line']): ?> on line <strong><?= $value['line'] ?></strong><?php endif ?>
                                <?php /* echo CDebug::renderSourceCode($value['file'], $value['line'], 25);*/ ?>
                                <br/>
                            <?php endforeach ?>
                        </div>
                    </div>
                </div>
            <?php endforeach ?>
        <?php endif ?>
    </div>
    <div class=error_box_footer>
        <span class=complete_time>Generated in {time} sec.</span>
        <span class=copy>{info}</span>
    </div>
</div>

</body>
</html>
