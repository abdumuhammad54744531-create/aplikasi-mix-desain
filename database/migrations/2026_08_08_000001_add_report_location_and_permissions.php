<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->text('location_description')->nullable()->after('location');
            $table->text('location_address')->nullable()->after('location_description');
            $table->decimal('latitude', 10, 7)->nullable()->after('location_address');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            $table->string('coordinate_format', 20)->default('decimal')->after('longitude');
            $table->string('map_image')->nullable()->after('coordinate_format');
            $table->string('map_caption')->nullable()->after('map_image');
        });

        DB::table('projects')->whereNull('location_address')->update(['location_address' => DB::raw('location')]);

        Schema::table('report_settings', function (Blueprint $table) {
            $table->string('signer_identity')->nullable()->after('signer_position');
            $table->text('examiner_address')->nullable()->after('signer_identity');
            $table->string('examiner_city')->nullable()->after('examiner_address');
            $table->string('examiner_province')->nullable()->after('examiner_city');
            $table->string('examiner_postal_code', 20)->nullable()->after('examiner_province');
            $table->string('examiner_phone', 100)->nullable()->after('examiner_postal_code');
            $table->string('examiner_email')->nullable()->after('examiner_phone');
            $table->string('examiner_website')->nullable()->after('examiner_email');
            $table->json('header_lines')->nullable()->after('examiner_website');
            $table->decimal('logo_left_height', 5, 1)->nullable()->after('logo_left_width');
            $table->decimal('logo_left_x', 6, 1)->default(0)->after('logo_left_height');
            $table->decimal('logo_left_y', 6, 1)->default(0)->after('logo_left_x');
            $table->decimal('logo_right_height', 5, 1)->nullable()->after('logo_right_width');
            $table->decimal('logo_right_x', 6, 1)->default(0)->after('logo_right_height');
            $table->decimal('logo_right_y', 6, 1)->default(0)->after('logo_right_x');
            $table->boolean('header_lines_enabled')->default(true);
            $table->decimal('header_line_1_width', 4, 1)->default(1.5);
            $table->decimal('header_line_2_width', 4, 1)->default(0.6);
            $table->decimal('header_line_gap', 4, 1)->default(1.2);
            $table->decimal('header_to_line_gap', 5, 1)->default(3);
            $table->decimal('line_to_content_gap', 5, 1)->default(5);
            $table->decimal('report_heading_size', 4, 1)->default(14);
            $table->decimal('report_subheading_size', 4, 1)->default(12);
            $table->decimal('report_table_size', 4, 1)->default(10.5);
            $table->decimal('report_caption_size', 4, 1)->default(10);
            $table->decimal('report_line_height', 4, 2)->default(1.15);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->json('permissions')->nullable()->after('access_level');
        });
    }

    public function down(): void
    {
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn('permissions'));
        Schema::table('report_settings', function (Blueprint $table) {
            $table->dropColumn([
                'signer_identity', 'examiner_address', 'examiner_city', 'examiner_province',
                'examiner_postal_code', 'examiner_phone', 'examiner_email', 'examiner_website',
                'header_lines', 'logo_left_height', 'logo_left_x', 'logo_left_y',
                'logo_right_height', 'logo_right_x', 'logo_right_y', 'header_lines_enabled',
                'header_line_1_width', 'header_line_2_width', 'header_line_gap',
                'header_to_line_gap', 'line_to_content_gap', 'report_heading_size',
                'report_subheading_size', 'report_table_size', 'report_caption_size', 'report_line_height',
            ]);
        });
        Schema::table('projects', fn (Blueprint $table) => $table->dropColumn([
            'location_description', 'location_address', 'latitude', 'longitude',
            'coordinate_format', 'map_image', 'map_caption',
        ]));
    }
};
