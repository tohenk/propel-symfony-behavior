if ($callable = <?= $method ?>('<?= $class ?>:'.$name)) {
    array_unshift($params, $this);
    return call_user_func_array($callable, $params);
}
