<?php
namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;

class TagsTextType extends AbstractType
{
	/**
	 * @var RouterInterface $route
	 */
	private $router;
	
	/**
	 * @param RouterInterface $router
	 */
	public function __construct(RouterInterface $router)
	{
		$this->router = $router;
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefaults([
			'required' => false,
			'label' => 'Tags',
			'attr' => [
				'placeholder' => 'Заполнять через запятую (пример: один, два, три)',
				'data-ajax' => $this->router->generate('tags_list'),
			],
		]);
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function getParent()
	{
		return TextType::class;
	}
}