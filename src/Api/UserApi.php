<?php

namespace App\Api;

use App\Catrobat\Services\TokenGenerator;
use App\Entity\User;
use App\Entity\UserManager;
use App\Utils\APIHelper;
use Exception;
use OpenAPI\Server\Api\UserApiInterface;
use OpenAPI\Server\Model\BasicUserDataResponse;
use OpenAPI\Server\Model\ExtendedUserDataResponse;
use OpenAPI\Server\Model\RegisterErrorResponse;
use OpenAPI\Server\Model\RegisterRequest;
use OpenAPI\Server\Model\UpdateUserRequest;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserApi implements UserApiInterface
{
  private string $token;

  private ValidatorInterface $validator;

  private UserManager $user_manager;

  private TokenGenerator $token_generator;

  private TranslatorInterface $translator;

  public function __construct(ValidatorInterface $validator, UserManager $user_manager, TokenGenerator $token_generator,
                              TranslatorInterface $translator)
  {
    $this->validator = $validator;
    $this->user_manager = $user_manager;
    $this->token_generator = $token_generator;
    $this->translator = $translator;
  }

  /**
   * {@inheritdoc}
   *
   * @throws Exception
   */
  public function setPandaAuth($value): void
  {
    $this->token = APIHelper::getPandaAuth($value);
  }

  /**
   * {@inheritdoc}
   */
  public function userPost(RegisterRequest $register, string $accept_language = null, &$responseCode, array &$responseHeaders)
  {
    $accept_language = APIHelper::setDefaultAcceptLanguageOnNull($accept_language);

    $validation_schema = $this->validateRegistration($register);

    if ($validation_schema->getEmail() || $validation_schema->getUsername() || $validation_schema->getPassword())
    {
      $responseCode = Response::HTTP_UNPROCESSABLE_ENTITY; // 422 => Unprocessable entity

      return $validation_schema;
    }
    if ($register->isDryRun())
    {
      $responseCode = Response::HTTP_NO_CONTENT; // 204 => Dry-run successful, no validation error
    }
    else
    {
      // Validation successful, no dry-run requested => we can actually register the user
      /** @var User $user */
      $user = $this->user_manager->createUser();
      $user->setUsername($register->getUsername());
      $user->setEmail($register->getEmail());
      $user->setPlainPassword($register->getPassword());
      $user->setEnabled(true);
      $user->setUploadToken($this->token_generator->generateToken());
      $this->user_manager->updateUser($user);
      $responseCode = Response::HTTP_CREATED; // 201 => User successfully registered
    }

    return null;
  }

  /**
   * Validates the Register object passed by the request. No automatic validation provided by the OpenApi
   * will be used cause non standard validations (e.g. validation if a username doesn't exist already) must be
   * used here.
   *
   * $accept_language -> The language used for translating the validation error messages
   *
   * @return RegisterErrorResponse The RegisterErrorResponse containing possible validation errors
   */
  public function validateRegistration(RegisterRequest $register): RegisterErrorResponse
  {
    $response = new RegisterErrorResponse();

    // E-Mail
    if (0 === strlen($register->getEmail()))
    {
      $response->setEmail($this->translator->trans('api.registerUser.emailMissing', [], 'catroweb'));
    }
    elseif (0 !== count($this->validator->validate($register->getEmail(), new Email())))
    {
      $response->setEmail($this->translator->trans('api.registerUser.emailInvalid', [], 'catroweb'));
    }
    elseif (null != $this->user_manager->findUserByEmail($register->getEmail()))
    {
      $response->setEmail($this->translator->trans('api.registerUser.emailAlreadyInUse', [], 'catroweb'));
    }

    // Username
    if (0 === strlen($register->getUsername()))
    {
      $response->setUsername($this->translator->trans('api.registerUser.usernameMissing', [], 'catroweb'));
    }
    elseif (strlen($register->getUsername()) < 3)
    {
      $response->setUsername($this->translator->trans('api.registerUser.usernameTooShort', [], 'catroweb'));
    }
    elseif (strlen($register->getUsername()) > 180)
    {
      $response->setUsername($this->translator->trans('api.registerUser.usernameTooLong', [], 'catroweb'));
    }
    elseif (filter_var(str_replace(' ', '', $register->getUsername()), FILTER_VALIDATE_EMAIL))
    {
      $response->setUsername($this->translator->trans('api.registerUser.usernameContainsEmail', [], 'catroweb'));
    }
    elseif (null != $this->user_manager->findUserByUsername($register->getUsername()))
    {
      $response->setUsername($this->translator->trans('api.registerUser.usernameAlreadyInUse', [], 'catroweb'));
    }
    elseif (0 === strncasecmp($register->getUsername(), User::$SCRATCH_PREFIX, strlen(User::$SCRATCH_PREFIX)))
    {
      $response->setUsername($this->translator->trans('api.registerUser.usernameInvalid', [], 'catroweb'));
    }

    // Password
    if (0 === strlen($register->getPassword()))
    {
      $response->setPassword($this->translator->trans('api.registerUser.passwordMissing', [], 'catroweb'));
    }
    elseif (strlen($register->getPassword()) < 6)
    {
      $response->setPassword($this->translator->trans('api.registerUser.passwordTooShort', [], 'catroweb'));
    }
    elseif (strlen($register->getPassword()) > 4_096)
    {
      $response->setPassword($this->translator->trans('api.registerUser.passwordTooLong', [], 'catroweb'));
    }
    elseif (!mb_detect_encoding($register->getPassword(), 'ASCII', true))
    {
      $response->setPassword($this->translator->trans('api.registerUser.passwordInvalidChars', [], 'catroweb'));
    }

    return $response;
  }

  public function userDelete(&$responseCode, array &$responseHeaders)
  {
    // TODO: Implement userDelete() method.
    $responseCode = Response::HTTP_NOT_IMPLEMENTED;
  }

  public function userGet(&$responseCode, array &$responseHeaders)
  {
    // TODO: Implement userGet() method.
    $responseCode = Response::HTTP_NOT_IMPLEMENTED;

    return new ExtendedUserDataResponse();
  }

  public function userIdGet(string $id, &$responseCode, array &$responseHeaders)
  {
    // TODO: Implement userIdGet() method.
    $responseCode = Response::HTTP_NOT_IMPLEMENTED;

    return new BasicUserDataResponse();
  }

  public function userPut(UpdateUserRequest $update_user_request, string $accept_language = null, &$responseCode, array &$responseHeaders)
  {
    $accept_language = APIHelper::setDefaultAcceptLanguageOnNull($accept_language);

    // TODO: Implement userPut() method.
    $responseCode = Response::HTTP_NOT_IMPLEMENTED;

    return null;
  }

  public function usersSearchGet(string $query, int $limit = 20, int $offset = 0, &$responseCode, array &$responseHeaders)
  {
    // TODO: Implement usersSearchGet() method.
    $responseCode = Response::HTTP_NOT_IMPLEMENTED;

    return []; // ...[] =  new BasicUserDataResponse()
  }
}
