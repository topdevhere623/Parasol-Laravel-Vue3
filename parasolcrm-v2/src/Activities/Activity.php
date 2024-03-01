<?php

namespace ParasolCRMV2\Activities;

use App\Models\Activity as Driver;
use App\Models\BackofficeUser;
use App\Models\BackofficeUserProgramAdmin;
use App\Models\Club\BackofficeUserClubAdmin;
use App\Models\Member\Junior;
use App\Models\Member\Member;
use App\Models\Member\MemberPrimary;
use App\Models\Member\Partner;
use App\Models\System;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Activity
{
    protected const EXCEPTED_COLUMNS = [
        'id',
        'created_at',
        'updated_at',
    ];

    protected $driver = null;

    protected $user = null;

    protected $entity = null;

    protected $parent = null;

    protected $userType = null;

    protected bool $activityLogEnabled = true;

    public function __construct()
    {
        $this->activityLogEnabled = !!config('logging.activity');
    }

    /**
     * Enable log writing
     */
    public function enable()
    {
        $this->activityLogEnabled = true;
    }

    /**
     * Disable log writing
     */
    public function disable()
    {
        $this->activityLogEnabled = false;
    }

    /**
     * Check Activity Log is enabled
     */
    public function isEnabled(): bool
    {
        return $this->activityLogEnabled;
    }

    /**
     * Start not necessary
     */
    public function watch()
    {
        $this->driver = $driver ?? new Driver();
    }

    /**
     * End
     */
    public function commit()
    {
        $this->driver = null;
        $this->entity = null;
        $this->parent = null;
        $this->user = null;
    }

    /**
     * @param  null  $user
     *
     * @return $this
     */
    public function user($user = null): self
    {
        $this->user = $user && ($user instanceof Model) ? $user : null;
        return $this;
    }

    /**
     * @param  null  $parent
     *
     * @return $this
     */
    protected function parent($parent): self
    {
        $this->parent = $parent;
        return $this;
    }

    /**
     * @param $entity
     *
     * @return $this
     */
    protected function entity($entity): self
    {
        $this->entity = $entity;
        return $this;
    }

    /**
     * Start not necessary
     */
    public function userType(string $userType): self
    {
        $this->userType = $userType;
        return $this;
    }

    /**
     * @return void
     */
    protected function setUser(): void
    {
        $this->driver->user_id = $this->user ? $this->user->id : (Auth::id() ?? System::DEFAULT_SYSTEM_ID);
        $this->driver->user_type
            = $this->user ? $this->user->getMorphClass() : ($this->userType ?? (Auth::user() ? Auth::user()
                ->getMorphClass() : System::class));
    }

    /**
     * @return void
     */
    protected function setParent(): void
    {
        $parent = $this->parent;

        if (is_string($parent) || is_int($parent)) {
            $this->driver->parent_id = $parent;
        }
        if ($parent instanceof Model) {
            $this->driver->parent_id = $parent->id;
        }
    }

    /**
     * @return void
     */
    protected function setEntity(): void
    {
        $this->driver->entity_id = $this->entity->getKey();
        $this->driver->entity_type = $this->entity->getMorphClass();
    }

    /**
     * @return array
     */
    protected function getChangesExceptColumns(): array
    {
        return array_filter($this->entity->getChanges(), function ($item) {
            return $this->checkAttributeAvailability($item);
        }, ARRAY_FILTER_USE_KEY);
    }

    /**
     * @return array
     */
    protected function getAttributesExceptColumns(): array
    {
        return array_filter($this->entity->getAttributes(), function ($item) {
            return $this->checkAttributeAvailability($item);
        }, ARRAY_FILTER_USE_KEY);
    }

    /**
     * @param $item
     *
     * @return bool
     */
    protected function checkAttributeAvailability($item): bool
    {
        if (in_array($item, self::EXCEPTED_COLUMNS)) {
            return false;
        }
        if (property_exists($this->entity, 'activityAttributes')) {
            return in_array($item, $this->entity->activityAttributes);
        }
        if (property_exists($this->entity, 'activityExceptAttributes')) {
            return !in_array($item, $this->entity->activityExceptAttributes);
        }
        return true;
    }

    protected function setDataFromModel($name): void
    {
        $data = [];

        if ($name === 'created') {
            foreach ($this->getAttributesExceptColumns() as $key => $value) {
                $data[$key] = [
                    'new_val' => $this->getValue($key, $value),
                    'label' => $this->getLabel($key),
                ];
            }
        }

        if ($name === 'updated') {
            $original = $this->entity->getRawOriginal();
            foreach ($this->getChangesExceptColumns() as $key => $value) {
                $data[$key] = [
                    'old_val' => $this->getValue($key, $original[$key] ?? ''),
                    'new_val' => $this->getValue($key, $value),
                    'label' => $this->getLabel($key),
                ];
            }
        }

        if ($name === 'deleted') {
            foreach ($this->getAttributesExceptColumns() as $key => $value) {
                $data[$key] = [
                    'old_val' => $this->getValue($key, $value),
                    'label' => $this->getLabel($key),
                ];
            }
        }

        $this->driver->data = $data;
    }

    /**
     * @param $name
     *
     * @return bool
     */
    protected function checkSave($name): bool
    {
        if (!$this->activityLogEnabled) {
            return true;
        }

        if (property_exists($this->entity, 'activityActive') && !$this->entity->activityActive) {
            return true;
        }
        return (bool) ($this->parent && $name === 'updated' && !count($this->entity->getChanges()));
    }

    public function save(string $name, $entity, string $description = '')
    {
        $this->entity = $entity;

        if ($this->checkSave($name)) {
            return;
        }

        if (!$this->driver) {
            $this->watch();
        }

        $this->driver->name = $name;
        $this->driver->description = !empty($description) ? $description : class_basename($entity);

        $this->setParent();
        $this->setUser();
        $this->setEntity();
        $this->setDataFromModel($name);

        $this->driver->save();

        if (!$this->parent) {
            $this->parent = $this->driver;
        }

        $this->watch();
    }

    public function created($entity, string $description = '')
    {
        return $this->save('created', $entity, $description);
    }

    public function updated($entity, string $description = '')
    {
        return $this->save('updated', $entity, $description);
    }

    public function deleted($entity, string $description = '')
    {
        return $this->save('deleted', $entity, $description);
    }

    public function message($data, string $description = '')
    {
    }

    public function getLabel(string $key): string
    {
        $label = $this->entity->getLabel($key);
        return $label ?? str_replace('_', ' ', trim(ucfirst($key)));
    }

    /**
     * @param  string  $key
     * @param $value
     *
     * @return mixed
     */
    public function getValue(string $key, $value)
    {
        if (is_string($value) && empty($value)) {
            return $value;
        }

        $rules = $this->entity->activityRules($value);
        if ($rules && count($rules) && key_exists($key, $rules)) {
            $rule = $rules[$key];
            return is_callable($rule) ? call_user_func($rule) : $rule;
        }

        return $value;
    }

    public function build($model, $id, $from, $to): array
    {
        $activityLogs = Driver::filterDate($from, $to)
            ->where(function (Builder $builder) use ($model) {
                $parentModel = $this->getParentModel($model);
                if ($parentModel) {
                    $builder->where('entity_type', $model)
                        ->orWhere('entity_type', $parentModel);
                } else {
                    $builder->where('entity_type', $model);
                }
            })
            ->whereEntityId($id)
            ->with(['children', 'userable'])
            ->latest()
            ->paginate(25)
            ->toArray();

        if (count($activityLogs) && key_exists('data', $activityLogs)) {
            foreach ($activityLogs['data'] as $key => &$activityLog) {
                if (!count($activityLog['data']) && !count($activityLog['children'])) {
                    unset($activityLogs['data'][$key]);
                } else {
                    $user = $activityLog['userable'];
                    $activityLog['userable'] = [
                        'id' => $user['id'] ?? '',
                        'first_name' => $user['first_name'] ?? '',
                        'last_name' => $user['last_name'] ?? '',
                        'email' => $user['email'] ?? '',
                    ];
                }
            }
            return $activityLogs['data'] = array_values($activityLogs['data']);
        }
        return [];
    }

    protected function getParentModel(string $model): ?string
    {
        if ($model === MemberPrimary::class || $model === Partner::class || $model === Junior::class) {
            return Member::class;
        }
        if ($model === BackofficeUserClubAdmin::class || $model === BackofficeUserProgramAdmin::class) {
            return BackofficeUser::class;
        }
        return null;
    }
}
