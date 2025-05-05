<?php

namespace App\Form\DataTransformer;

use App\Entity\Course;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

readonly class CourseToIdTransformer implements DataTransformerInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }


    public function transform(mixed $courseId): int
    {
        return (int)$courseId;
    }

    public function reverseTransform(mixed $courseId): Course
    {
        $course = $this->entityManager->getRepository(Course::class)->find($courseId);

        if (null === $course) {
            $publicErrorMessage = sprintf('Курс с ID "%s" не найден', $courseId);
            $failure = new TransformationFailedException($publicErrorMessage);
            $failure->setInvalidMessage($publicErrorMessage);

            throw $failure;
        }

        return $course;
    }
}