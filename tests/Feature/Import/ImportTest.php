<?php

namespace Tests\Feature\Import;

use App\Http\Controllers\ImportController;
use App\Http\Requests\CsvImportRequest;
use App\Import\ModelMapping\MappingRegister;
use App\Import\ModelMapping\ModelMapping;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Mockery;
use Tests\Feature\Import\ModelMappings\ModelMappingBlog;
use Tests\Feature\Import\ModelMappings\ModelMappingPost;
use Tests\TestCase;

class ImportTest extends TestCase
{
    public const ROUTE_IMPORT = 'api/' . ImportController::ROUTE_IMPORT;

    /**
     * This test ensures the import create a user and two roles and also relates those.
     *
     * @return void
     */
    public function testUserAndRoleImport(): void
    {
        User::query()->create([
            'name' => 'Sebastian12',
            'password' => Hash::make('password!'),
            'email' => 'ws-1993@gmx.de'
        ]);

        $this->createAndActAsUser();
        $this->postJson(
            self::ROUTE_IMPORT,
            [CsvImportRequest::FILE => $this->getDefaultXlsx('UserImport.xlsx')],
        )->assertSuccessful();

        /** @var User $user */
        $user = User::query()->first();
        $this->assertEquals('Sebastian', $user->name);
        $this->assertTrue($user->hasRole('admin'));
        $this->assertTrue($user->hasRole('bidderroundparticipant'));
    }

    /**
     * This test ensures an exception gets thrown as soon as a column has been found which matches for more than one
     * regex.
     *
     * @dataProvider modelMappingProvider
     * @param ModelMapping $modelMapping
     * @return void
     */
    public function testOverlappingRegex(ModelMapping $modelMapping): void
    {
        $this->createAndActAsUser();

        /** @var MappingRegister $register */
        $register = resolve(MappingRegister::class);
        $register->register($modelMapping);

        $response = $this->postJson(
            self::ROUTE_IMPORT,
            [CsvImportRequest::FILE => $this->getDefaultXlsx('UserImport.xlsx')],
        );
        $this->assertTrue($response->isServerError());
        $this->assertStringContainsString(
            'The regex\'s result is overlapping. More than one matching regex',
            $response->json('message')
        );
    }

    public function testHookNotFound(): void
    {
        $this->createAndActAsUser();

        /** @var MappingRegister $register */
        $register = resolve(MappingRegister::class);

        $blogMock = Mockery::mock(Model::class)->makePartial();
        $blogMock->shouldReceive('save')->andReturn(true);
        $blogMock->shouldReceive('newInstance')->andReturn($blogMock);
        $blogMock->shouldReceive('getAttributes')->passthru();
        $blogBuilderMock = Mockery::mock(Builder::class);
        $blogBuilderMock->shouldReceive('updateOrCreate')->andReturn($blogMock);
        $blogMock->shouldReceive('newQuery')
            ->andReturn(
                $blogBuilderMock
            );

        $postMock = Mockery::mock(Model::class)->makePartial();
        $postMock->shouldReceive('save')->andReturn(true);
        $postMock->shouldReceive('newInstance')->andReturn($postMock);
        $postMock->shouldReceive('getAttributes')->passthru();
        $postMock->fillable(['property']);
        $postBuilderMock = Mockery::mock(Builder::class);
        $postBuilderMock->shouldReceive('firstOrNew')->andReturn($postMock);
        $postMock->shouldReceive('newQuery')->andReturn($postBuilderMock);

        $register->register(new ModelMappingBlog($blogMock))
            ->register(new ModelMappingPost($postMock));

        $this->postJson(
            self::ROUTE_IMPORT,
            [CsvImportRequest::FILE => $this->getDefaultXlsx('PropertyImport.xlsx')],
        )->assertSuccessful();

        $this->assertFalse(ModelMappingPost::$hasHookBeenCalled);
    }

    public function modelMappingProvider(): array
    {
        return [
            'regex matching between two models' => [
                new class extends ModelMapping {
                    public function __construct()
                    {
                        parent::__construct(new User());
                    }

                    public function propertyMapping(): Collection
                    {
                        return collect([
                            'matchAll' => '/.*/i'
                        ]);
                    }

                    public function uniqueColumns(): array
                    {
                        return [];
                    }
                }
            ],
            'regex matching within same model' => [
                new class extends ModelMapping {
                    public function __construct()
                    {
                        parent::__construct(new User());
                    }

                    public function propertyMapping(): Collection
                    {
                        return collect([
                            'productNumber' => '/Product Number/i',
                            'userNumber' => '/Number/i'
                        ]);
                    }

                    public function uniqueColumns(): array
                    {
                        return [];
                    }
                }
            ]
        ];
    }

    private function getDefaultXlsx(string $fileName): UploadedFile
    {
        return new UploadedFile(
            base_path('tests/assets/' . $fileName),
            $fileName,
            null,
            null,
            true
        );
    }
}
