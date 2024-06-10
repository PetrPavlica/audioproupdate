<?php

namespace HomeCredit\OneClickApi;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\MessageFormatter;
use GuzzleHttp\Middleware;
use Psr\Log\LoggerInterface;
use Respect\Validation\Validator;
use Symfony\Component\Console\Exception\InvalidOptionException;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HttpClientFactory
{

	/**
	 * @var mixed[]
	 */
	private $baseConfig = [];

	/**
	 * @var LoggerInterface|null
	 */
	private $logger;

	/**
	 * @var callable[]
	 */
	private $middlewareStack;

	/**
	 * @var HandlerStack
	 */
	private $handlerStack;

	/**
	 * @param mixed[] $baseConfig
	 * @param LoggerInterface $logger
	 * @param HandlerStack|null $handlerStack
	 */
	public function __construct(array $baseConfig, LoggerInterface $logger = null, HandlerStack $handlerStack = null)
	{
		$optionsResolver = new OptionsResolver();
		$this->configureOptions($optionsResolver);

		$this->baseConfig = $optionsResolver->resolve($baseConfig);
		$this->logger = $logger;
		$this->handlerStack = $handlerStack;
	}

	/**
	 * @param OptionsResolver $optionsResolver
	 */
	private function configureOptions(OptionsResolver $optionsResolver)
	{
		$optionsResolver->setRequired('baseUrl');
		$optionsResolver->setDefaults([
			'logMessageFormat' => "\">>>>>>>>\n{request}\n<<<<<<<<\n{response}\n--------\n{error}\""
		]);

		$optionsResolver->setNormalizer('baseUrl', function (Options $options, $value) {
			if (!Validator::url()->validate($value)) {
				throw new InvalidOptionException(sprintf('Value "%s" for "%s" option is not valid URL.', $value, 'baseUrl'));
			}
			return $value;
		});
	}

	/**
	 * @param mixed[] $config
	 * @param callable[] $middlewareStack
	 * @return Client
	 */
	public function getClient(array $config, array $middlewareStack = [])
	{
		if (!isset($config['handler'])) {
			if (!$this->handlerStack) {
				$stack = HandlerStack::create();
			} else {
				$stack = $this->handlerStack;
			}
		} else {
			$stack = $config['handler'];
		}

		if ($this->middlewareStack) {
			foreach ($this->middlewareStack as $middleware) {
				$stack->push($middleware);
			}
		}

		foreach ($middlewareStack as $middleware) {
			$stack->push($middleware);
		}

		if ($this->handlerStack) {
			$config['handler'] = $this->handlerStack;
		} else {
			$config['handler'] = $stack;
		}

		if ($this->logger) {
			$config['handler']->push(Middleware::log($this->logger, new MessageFormatter($this->baseConfig['logMessageFormat'])));
		}

		$config = array_merge(['base_uri' => $this->baseConfig['baseUrl']], $config);
		return new Client($config);
	}

}
