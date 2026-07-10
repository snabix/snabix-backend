<?php

declare(strict_types=1);

namespace App\Notification\Domain\Enums;

enum NotificationEventType: string
{
    case NEW_MESSAGES       = 'new_messages';
    case LISTING_REPLIES    = 'listing_replies';
    case LISTING_MODERATION = 'listing_moderation';
    case FAVORITE_LISTINGS  = 'favorite_listings';
    case LISTING_VIEWS      = 'listing_views';
    case RECOMMENDATIONS    = 'recommendations';
    case PRICE_CHANGES      = 'price_changes';
    case LISTING_EXPIRATION = 'listing_expiration';
    case SECURITY_LOGIN     = 'security_login';
    case PROMOTIONS_NEWS    = 'promotions_news';
    case EMAIL_DIGEST       = 'email_digest';

    public function category(): string
    {
        return match ($this) {
            self::NEW_MESSAGES                                                                       => 'messages',
            self::LISTING_REPLIES, self::LISTING_MODERATION, self::LISTING_EXPIRATION                => 'listings',
            self::FAVORITE_LISTINGS, self::LISTING_VIEWS, self::RECOMMENDATIONS, self::PRICE_CHANGES => 'activity',
            self::SECURITY_LOGIN, self::PROMOTIONS_NEWS, self::EMAIL_DIGEST                          => 'system',
        };
    }

    public function title(): string
    {
        return match ($this) {
            self::NEW_MESSAGES       => 'Новые сообщения',
            self::LISTING_REPLIES    => 'Ответы на объявления',
            self::LISTING_MODERATION => 'Модерация объявлений',
            self::FAVORITE_LISTINGS  => 'Избранные объявления',
            self::LISTING_VIEWS      => 'Просмотры объявления',
            self::RECOMMENDATIONS    => 'Подборки и рекомендации',
            self::PRICE_CHANGES      => 'Изменение цены',
            self::LISTING_EXPIRATION => 'Окончание срока публикации',
            self::SECURITY_LOGIN     => 'Безопасность и вход',
            self::PROMOTIONS_NEWS    => 'Акции и новости',
            self::EMAIL_DIGEST       => 'Email-рассылка',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::NEW_MESSAGES       => 'Новые сообщения от покупателей и продавцов.',
            self::LISTING_REPLIES    => 'Ответы, отклики и важные действия по объявлениям.',
            self::LISTING_MODERATION => 'Решения модерации: публикация, отклонение и архивирование объявлений.',
            self::FAVORITE_LISTINGS  => 'Изменения в объявлениях из избранного.',
            self::LISTING_VIEWS      => 'Активность просмотров ваших объявлений.',
            self::RECOMMENDATIONS    => 'Персональные предложения и подборки.',
            self::PRICE_CHANGES      => 'Изменения цены в избранных объявлениях.',
            self::LISTING_EXPIRATION => 'Напоминания об окончании публикации.',
            self::SECURITY_LOGIN     => 'Вход, смена пароля и важные действия с аккаунтом.',
            self::PROMOTIONS_NEWS    => 'Новости возможностей платформы и акции.',
            self::EMAIL_DIGEST       => 'Полезные подборки и важные новости по email.',
        };
    }

    public function defaultSiteEnabled(): bool
    {
        return ! in_array($this, [self::LISTING_VIEWS, self::PROMOTIONS_NEWS, self::EMAIL_DIGEST], true);
    }

    public function defaultEmailEnabled(): bool
    {
        return in_array($this, [
            self::NEW_MESSAGES,
            self::LISTING_REPLIES,
            self::LISTING_MODERATION,
            self::PRICE_CHANGES,
            self::LISTING_EXPIRATION,
            self::SECURITY_LOGIN,
            self::EMAIL_DIGEST,
        ], true);
    }

    public function isRequiredSite(): bool
    {
        return in_array($this, [self::SECURITY_LOGIN, self::LISTING_MODERATION], true);
    }

    public function isRequiredEmail(): bool
    {
        return $this === self::LISTING_MODERATION;
    }
}
