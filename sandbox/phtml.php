<ul>before<li>inside</li>after</ul>
12345
<f:input name="test" label="somelabel">1234123</f:input>
<f:input name="test" label="somelabel">
    1234123
    <f:input name="test" label="somelabel">1234123</f:input>
</f:input>
<?

echo "<f:input name='test' />";
?>

<w:panel title="TEST%{$test} æ'panel: %{$test}" test="%{$variable}">
    en lang tekst her med %{$vars} og eventuelt også noget
    en lang tekst her med %{$vars} og eventuelt også noget
    en lang tekst her med %{$vars} og eventuelt også noget
    en lang tekst her med %{$vars} og eventuelt også noget
    en lang tekst her med %{$vars} og eventuelt også noget
    %{$expr = (in_array($some,$thing)) ? "test" : "test 2" }
</w:panel>