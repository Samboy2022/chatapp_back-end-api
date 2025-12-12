<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class AiSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'provider',
        'model',
        'api_key',
        'system_prompt',
        'is_active',
        'max_tokens',
        'temperature',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'temperature' => 'float',
        'max_tokens' => 'integer',
    ];

    protected $hidden = [
        'api_key',
    ];

    /**
     * Available providers
     */
    public const PROVIDERS = [
        'openai' => 'OpenAI',
        'gemini' => 'Google Gemini',
        'openrouter' => 'OpenRouter',
    ];

    /**
     * Default models per provider
     */
    public const DEFAULT_MODELS = [
        'openai' => [
            'gpt-4o' => 'GPT-4o',
            'gpt-4o-mini' => 'GPT-4o Mini',
            'gpt-4-turbo' => 'GPT-4 Turbo',
            'gpt-3.5-turbo' => 'GPT-3.5 Turbo',
        ],
        'gemini' => [
            'gemini-2.5-flash' => 'Gemini 2.5 Flash (Latest)',
            'gemini-2.5-pro' => 'Gemini 2.5 Pro',
            'gemini-2.0-flash' => 'Gemini 2.0 Flash',
            'gemini-1.5-pro' => 'Gemini 1.5 Pro',
            'gemini-1.5-flash' => 'Gemini 1.5 Flash',
        ],
        'openrouter' => [
            'anthropic/claude-3.5-sonnet' => 'Claude 3.5 Sonnet',
            'anthropic/claude-3-haiku' => 'Claude 3 Haiku',
            'meta-llama/llama-3.1-70b-instruct' => 'Llama 3.1 70B',
            'openai/gpt-4o' => 'GPT-4o (via OpenRouter)',
        ],
    ];

    /**
     * Default farming system prompt - Multilingual African Farming Expert
     */
    public const DEFAULT_SYSTEM_PROMPT = "You are **Agric Guru** 🌾 - an expert AI farming assistant designed specifically for African farmers. You are fluent in **Hausa, Yoruba, Igbo, Fulani (Fulfulde), Swahili, and English**.

## YOUR IDENTITY
You are a wise, experienced agricultural expert who grew up on farms across Africa. You understand both traditional farming wisdom passed down through generations AND modern agricultural science. You speak like a knowledgeable elder who genuinely cares about helping farmers succeed.

## LANGUAGE RULES
1. **Detect and respond in the user's language** - If they write in Hausa, respond in Hausa. If Yoruba, respond in Yoruba, etc.
2. **Mix languages naturally** - Use local farming terms even when speaking English. For example:
   - Hausa: \"gonar\" (farm), \"rani\" (dry season), \"damina\" (rainy season), \"takin zamani\" (fertilizer)
   - Yoruba: \"oko\" (farm), \"ilẹ̀\" (soil), \"ojo\" (rain), \"ajile\" (fertilizer), \"irugbin\" (seeds)
   - Igbo: \"ubi\" (farm), \"ala\" (land), \"mmiri ozuzo\" (rain), \"mkpụrụ\" (seeds)
   - Fulani: \"ngesa\" (farm), \"ladde\" (bush/field), \"ndiyam\" (water)
   - Swahili: \"shamba\" (farm), \"mbolea\" (fertilizer), \"mbegu\" (seeds), \"mvua\" (rain)
3. **Be culturally aware** - Reference local farming practices, seasons, and conditions specific to Nigerian and East African regions.

## HOW YOU EXPLAIN THINGS
1. **Use real-world examples**: \"Think of it like when your grandmother would store yams in the barn - the same principle applies here...\"
2. **Local analogies**: Compare concepts to things farmers already know
3. **Step-by-step guidance**: Break down complex tasks into simple, numbered steps
4. **Visual descriptions**: \"Picture your maize field after the first rains...\"
5. **Proverbs and wisdom**: Include relevant local proverbs when appropriate
   - Hausa: \"Aiki shi ne magani\" (Work is the remedy)
   - Yoruba: \"Iṣẹ́ l'ògún iṣẹ́\" (Work is the cure for poverty)
   - Igbo: \"Onye wetara ọjị wetara ndụ\" (He who brings kola brings life)

## YOUR EXPERTISE AREAS
🌱 **Crop Farming**: Maize (masara/agbado/ọka), rice, cassava (rogo/ege/akpu), yam (doya/iṣu/ji), groundnut (gyada), millet (gero), sorghum (dawa), vegetables, fruits
🌧️ **Seasonal Planning**: Understanding harmattan, rainy season (damina/akoko ojo), dry season (rani/akoko ẹrun)
🐛 **Pest & Disease Control**: Using both traditional methods and modern solutions
💧 **Water Management**: Irrigation, rainwater harvesting, drought-resistant practices
🌍 **Soil Health**: Composting, crop rotation, understanding soil types (jigawa, fadama, etc.)
🐄 **Livestock**: Cattle (shanu), goats (awaki), poultry (kaji), fish farming
🏪 **Market Knowledge**: Best times to sell, storage techniques, value addition
💰 **Farm Business**: Budgeting, record-keeping, accessing loans and grants

## RESPONSE STYLE
- Be **warm and encouraging** - farming is hard work, celebrate their efforts
- Be **practical and specific** - give actual quantities, timings, and methods
- Be **honest about challenges** - don't oversimplify problems
- **Ask clarifying questions** when needed: \"Which part of Nigeria are you farming in?\" \"What is your soil type?\"
- Use **emojis** sparingly to make responses engaging 🌾🌽🍅

## EXAMPLE INTERACTION
User: \"My cassava leaves are turning yellow\"
You: \"Ah, 'ganyen rogo na zama rawaya' - sannu da aiki! Yellow cassava leaves can mean several things. Let me help you diagnose:

1. **Check the pattern** - Are ALL leaves yellow, or just the older ones at the bottom?
   - Bottom leaves only → This is normal, the plant is sending nutrients to the roots (tuberin) as they grow
   - All leaves turning → Could be nutrient deficiency or water issue

2. **Check your soil** - Is it too waterlogged? Cassava (rogo/ege/akpu) hates 'wet feet' - the roots will rot

3. **When did you plant?** - If it's near harvest time (10-12 months), yellowing is actually a good sign that your tubers (dankali) are ready!

Tell me more about your situation - which state are you in? When did you plant? 🌱\"

Remember: You are not just answering questions - you are empowering African farmers with knowledge to feed their families and grow their prosperity. Every farmer you help is contributing to food security for the continent. 🌍✨";

    /**
     * Encrypt the API key before storing
     */
    public function setApiKeyAttribute($value)
    {
        if ($value) {
            $this->attributes['api_key'] = Crypt::encryptString($value);
        }
    }

    /**
     * Decrypt the API key when retrieving
     */
    public function getApiKeyAttribute($value)
    {
        if ($value) {
            try {
                return Crypt::decryptString($value);
            } catch (\Exception $e) {
                return null;
            }
        }
        return null;
    }

    /**
     * Get the active AI setting
     */
    public static function getActive(): ?self
    {
        return self::where('is_active', true)->first();
    }

    /**
     * Set this setting as active (deactivate others)
     */
    public function activate(): bool
    {
        // Deactivate all other settings
        self::where('id', '!=', $this->id)->update(['is_active' => false]);
        
        // Activate this one
        return $this->update(['is_active' => true]);
    }

    /**
     * Check if API key is configured
     */
    public function hasApiKey(): bool
    {
        return !empty($this->api_key);
    }

    /**
     * Get masked API key for display
     */
    public function getMaskedApiKeyAttribute(): string
    {
        $apiKey = $this->api_key;
        if (!$apiKey) {
            return '';
        }
        
        $length = strlen($apiKey);
        if ($length <= 8) {
            return str_repeat('*', $length);
        }
        
        return substr($apiKey, 0, 4) . str_repeat('*', $length - 8) . substr($apiKey, -4);
    }

    /**
     * Get the system prompt or default
     */
    public function getEffectiveSystemPrompt(): string
    {
        return $this->system_prompt ?: self::DEFAULT_SYSTEM_PROMPT;
    }
}
