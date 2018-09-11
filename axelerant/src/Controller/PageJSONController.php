<?php

namespace Drupal\axelerant\Controller;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * PageJSONController controller.
 */
class PageJSONController extends ControllerBase {

  /**
   * @var \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   */
  private $config_factory;

  /**
   * @var \Symfony\Component\Serializer\SerializerInterface $serializer
   */
  private $serializer;

  /**
   * PageJSONController constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *  Config factory service.
   *
   * @param \Symfony\Component\Serializer\SerializerInterface $serializer
   *  Serializer service.
   */
  public function __construct(ConfigFactoryInterface $config_factory, SerializerInterface $serializer) {
    $this->config_factory = $config_factory;
    $this->serializer = $serializer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      // Load ConfigFactory service from Container.
      $container->get('config.factory'),
      // Load Serializer service from Container.
	  $container->get('serializer')
    );
  }

  /**
   * returns JSON representation of a given node with
   * the content type "page"
   *
   * @param string $siteapikey
   *  Site api key.
   *
   * @param $node
   *  Node id.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *  Return Json formatted Response.
   */
  public function getPageJSON($siteapikey, $node) {
    $site_api_key = $this->config_factory->get('axelerant.settings')->get('siteapikey');
    if ($site_api_key === $siteapikey) {
      $node1 = Node::load($node);
      if (!empty($node1) && $node1->bundle() === 'page') {
        $data = $this->serializer->serialize($node1, 'json');
        return new JsonResponse(json_decode($data));
      } else {
        return new JsonResponse($this->getPageAccessDenied());
      }
    }
    return new JsonResponse($this->getPageAccessDenied());
  }

  /**
   * returns 'Access Denied' Response.
   *
   * @return array
   * Returns markup for access denied error.
   */
  public function getPageAccessDenied() {
    return [
      '#markup' => $this->t('Invalid api key or Node Id.'),
    ];
  }
}
