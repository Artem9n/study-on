<?php

namespace App\Form;

use App\Entity\Lesson;
use App\Form\DataTransformer\CourseToIdTransformer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LessonType extends AbstractType
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('content', TextareaType::class)
            ->add('orderNumber', IntegerType::class);

        if (!empty($options['course_id'])) {
            $builder
                ->add('course', HiddenType::class, [
                    'data' => $options['course_id'],
                ])
                ->get('course')->addModelTransformer(new CourseToIdTransformer($this->entityManager));
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Lesson::class,
            'course_id' => false,
        ]);
    }
}
