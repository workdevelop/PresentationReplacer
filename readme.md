# PRESENTATION REPLACER

## Annotation

This package replaces pre-defined variables in presentations with data
This package is suitable not only for vitalize presentation templates, but also for any other zip archives with files, contained pre-defined strings
printing_args

## Example
File example.pptx is template and has pre-defined variables at some src file\
chart2_datatype1_0, chart2_datatype1_1, chart2_datatype1_2 like this 
![](http://i.imgur.com/p3l3YV5.png)

Preparation
<pre>
$createPresentation = new \PresentationReplacer\CreatePresentation();
$templatePath = __DIR__.'/../storage/example.pptx';
$resultPath = __DIR__.'/../storage/result/result'.date('Ymd_His').'.pptx';
$createPresentation->setTemplatePath($templatePath);
$createPresentation->setResultPath($resultPath);
$createPresentation->setVariableRegex('/(\{char.*?\})/');
</pre>

Get pre-defined variables at presentation
<pre>
try {
    $allVariables = $createPresentation->getAllVariablesAtPresentation();
} catch (PresentationReplacer\PptException $exception) {
    //do something
}
</pre>

To replace pre-defined variable real data and download 
<pre>
try {
    $createPresentation->replaceVariables([
        '{chart2_datatype1_0}' => 500,
        '{chart2_datatype1_1}' => 501
        '{chart2_datatype1_2}' => 100
    ]);
} catch (PresentationReplacer\PptException $exception) {
    //do something
}
$createPresentation->download('ready_presentation.pptx');
</pre>

## How to prepare presentation template 
coming soon 