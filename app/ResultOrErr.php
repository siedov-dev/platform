<?php

namespace App;

/**
 * Небросающий-исключения контейнер "результат или ошибка".
 *
 * @template TResult
 */
class ResultOrErr
{
    /**
     * Результат успешной операции (если $success === true).
     *
     * @var TResult|null
     */
    private mixed $result = null;

    /**
     * Текст ошибки (обычно непустой, если $success === false).
     */
    private string $error;

    /**
     * Признак успеха операции.
     */
    private bool $success;

    /**
     * Создать объект с ошибкой.
     *
     * @param string $err Сообщение об ошибке
     * @return self<null>
     */
    public static function error(string $err): ResultOrErr
    {
        return new self(null, $err, false);
    }

    /**
     * Создать объект с успешным результатом.
     *
     * @param TResult $result
     * @return self<TResult>
     */
    public static function success(mixed $result): ResultOrErr
    {
        return new self($result, "", true);
    }

    /**
     * @param TResult|null $result
     * @param string $error
     * @param bool $success
     */
    private function __construct(mixed $result, string $error, bool $success)
    {
        $this->result = $result;
        $this->error = $error;
        $this->success = $success;
    }

    /**
     * Возвращает результат.
     * Вызывающему коду обычно стоит проверять isSuccess() перед чтением результата.
     *
     * @return TResult|null
     */
    public function getResult(): mixed
    {
        return $this->result;
    }

    public function setResult($result): void
    {
        $this->result = $result;
    }

    /**
     * Возвращает текст ошибки (может быть пустым при успехе).
     */
    public function getError(): string
    {
        return $this->error;
    }

    /**
     * true — успех, false — ошибка.
     */
    public function isSuccess(): bool
    {
        return $this->success;
    }
}
