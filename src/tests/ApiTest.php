<?php

use Mmal\OpenapiValidator\Validator;
use Symfony\Component\Yaml\Yaml;

/**
 * Проверка API на соответсвие спецификации
 */
class TestApi extends TestCase
{
    const SPEC_PATH = __DIR__.'/../../spec-v1.yaml';
    
    /** @var Validator */
    static $openaApiValidator;

    static public function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        self::$openaApiValidator = new Validator(Yaml::parse(file_get_contents(self::SPEC_PATH)));
    }

    /**
     * Успешное выполнение ping метода
     *
     * @return void
     */
	public function testPing()
	{
		$this->makeRequest('GET', '/ping');
	}

	/**
	 * Успешное выполнение метода получения списка контейнеров
	 *
	 * @return void
	 */
	public function testListContainersSuccess()
	{
		$this->listContainers(true);
	}

	/**
	 * Получение ошибки при выполнение метода получения списка контейнеров
	 *
	 * @return void
	 */
	public function testListContainersError()
	{
		$this->listContainers(false);
	}

	/**
	 * Запуск тестов для получения списка контейнеров
	 *
	 * @param  bool $testSuccess Нужно ли получить корректный результат
	 * @return void
	 */
	protected function listContainers(bool $testSuccess)
	{
		$this->makeRequest('POST', '/v1/containers', $this->getContainerData(1, $testSuccess));
		$this->makeRequest('POST', '/v1/containers', $this->getContainerData(2, $testSuccess));
		$this->makeRequest('POST', '/v1/containers', $this->getContainerData(3, $testSuccess));
		$this->makeRequest('GET', '/v1/containers', ['123']);
	}

	/**
	 * Успешное выполнение метода создания контейнера
	 *
	 * @return void
	 */
	public function testCreateContainersSuccess()
	{
		$this->createContainers(true);
	}

	/**
	 * Получение ошибки при выполнение метода создания контейнера
	 *
	 * @return void
	 */
	public function testCreateContainersError()
	{
		$this->createContainers(false);
	}

	/**
	 * Запуск тестов для создания контейнера
	 *
	 * @param  bool $testSuccess Нужно ли получить корректный результат
	 * @return void
	 */
	protected function createContainers(bool $testSuccess)
	{
		$this->makeRequest('POST', '/v1/containers', $this->getContainerData(1, $testSuccess));
	}

	/**
	 * Успешное выполнение метода получения одного контейнера
	 *
	 * @return void
	 */
	public function testShowContainerByIdSuccess()
	{
		$this->showContainerById(true);
	}

	/**
	 * Получение ошибки при выполнение метода получения одного контейнера
	 *
	 * @return void
	 */
	public function testShowContainerByIdError()
	{
		$this->showContainerById(false);
	}

	/**
	 * Запуск тестов для получения одного контейнера
	 *
	 * @param  bool $testSuccess Нужно ли получить корректный результат
	 * @return void
	 */
	protected function showContainerById(bool $testSuccess)
	{
		$this->makeRequest('POST', '/v1/containers', $this->getContainerData(1, $testSuccess));

		$this->makeRequest('GET', sprintf('/v1/containers/%s', 1));
	}

	/**
	 * Получение массива с данными контейнера
	 * Имитация запроса на сервер или ответа сервер
	 *
	 * @param  int|integer $containerId ID контейнера
	 * @param  bool        $testSuccess Нужно ли получить корректный результат
	 * @return array
	 */
	protected function getContainerData(int $containerId = 1, bool $testSuccess)
	{
		$products = [];
		for ($i=0; $i < 10; $i++) {
			// Заранее делаем ошибку, чтобы получить отрицательный результат, если это необходимо
			if ($testSuccess) {
				$productId = $containerId * $i;
			} else {
				$productId = 'error' . $containerId * $i;
			}
			$products[] = [
				'id' => $productId,
				'name' => sprintf('Product %s', $productId),
			];
		}

		return [
			'id' => $containerId,
			'name' => sprintf('Container %s', $containerId),
			'products' => $products,
		];
	}

	/**
	 * Имитация запроса на сервер
	 * @param  string Метод
	 * @param  string URI для запроса
	 * @param  array Контент, если это POST запрос
	 * @return void
	 */
    protected function makeRequest($method, $uri, $content = [])
    {
        $response = $this->json($method, $uri, $content);

        // Так как префикс версии находится в server url, а для запроса он необходим
        // убираем его, когда передаем uri в валидатор
        $uri = str_replace('/v1', '', $uri);

        $result = self::$openaApiValidator->validateBasedOnRequest(
            $uri,
            $method,
            $response->response->getStatusCode(),
            json_decode($response->response->getContent(), true)
        );

        self::assertFalse($result->hasErrors(), $result);

        return $response;
    }
}
