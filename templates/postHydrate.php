foreach (<?= $callables ?>(self::class, ':hydrate:post') as $callable) {
    call_user_func($callable, $this);
}
