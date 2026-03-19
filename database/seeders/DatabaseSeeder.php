    {
        $admin = User::query()->create([
            'name' => 'Admin',
            'surname' => 'User',
            'email' => 'admin@libver.test',
            'password' => Hash::make('admin123'),
            'role' => User::ROLE_ADMIN,
            'phone' => '6900000001',
            'card_number' => 'LIB-ADMIN-001',
            'dob' => '1985-04-12',
            'email_verified_at' => now(),
        ]);

        $parent = User::query()->create([
            'name' => 'Maria',
            'surname' => 'Nikolou',
            'email' => 'maria.parent@libver.test',
            'password' => Hash::make('parent123'),
            'role' => User::ROLE_PARENT,
            'phone' => '6900000002',
            'card_number' => 'LIB-00021',
            'dob' => '1990-09-15',
            'email_verified_at' => now(),
        ]);

        $childA = Child::query()->create([
            'user_id' => $parent->id,
            'first_name' => 'Eleni',
            'last_name' => 'Nikolou',
            'dob' => '2017-06-03',
            'phone_emergency' => '6900000009',
        ]);

        $childB = Child::query()->create([
            'user_id' => $parent->id,
            'first_name' => 'Nikos',
            'last_name' => 'Nikolou',
            'dob' => '2014-11-21',
            'phone_emergency' => '6900000009',
        ]);

        $startA = now()->addDays(6)->setTime(17, 0);
        $endA = (clone $startA)->addHour();
        $activityA = Activity::query()->create([
            'title' => 'The hidden rhythm of the Alhambra',
            'description_html' => '<p>Explore rhythm, pattern, and storytelling with hands-on crafts inspired by the Alhambra.</p>',
            'type' => 'workshop',
            'age_group' => '5-6',
            'status' => 'active',
            'capacity' => 5,
            'waitlist_enabled' => true,
            'reg_start_at' => now()->subDay(),
            'start_at' => $startA,
            'end_at' => $endA,
            'location' => 'Magic Boxes',
        ]);

        ActivitySession::query()->create([
            'activity_id' => $activityA->id,
            'mode' => 'in_person',
            'start_at' => $startA,
            'end_at' => $endA,
            'location' => 'Magic Boxes',
        ]);

        $startB = now()->addDays(6)->setTime(18, 30);
        $endB = (clone $startB)->addHour();
        $activityB = Activity::query()->create([
            'title' => 'The code of the Malaga bull',
            'description_html' => '<p>Decode shapes and symbols from Picasso to discover the strange bull puzzle.</p>',
            'type' => 'workshop',
            'age_group' => '7-11',
            'status' => 'active',
            'capacity' => 5,
            'waitlist_enabled' => true,
            'reg_start_at' => now()->subDay(),
            'start_at' => $startB,
            'end_at' => $endB,
            'location' => 'Magic Boxes',
        ]);

        ActivitySession::query()->create([
            'activity_id' => $activityB->id,
            'mode' => 'in_person',
            'start_at' => $startB,
            'end_at' => $endB,
            'location' => 'Magic Boxes',
        ]);

        $startC = now()->addDays(13)->setTime(17, 0);
        $endC = (clone $startC)->addHour();
        $activityC = Activity::query()->create([
            'title' => 'The silent garden',
            'description_html' => '<p>Nature-inspired art and mindfulness for ages 6-9.</p>',
            'type' => 'workshop',
            'age_group' => '6-9',
            'status' => 'active',
            'capacity' => 8,
            'waitlist_enabled' => true,
            'reg_start_at' => now()->subDay(),
            'start_at' => $startC,
            'end_at' => $endC,
            'location' => 'Main Hall',
        ]);

        ActivitySession::query()->create([
            'activity_id' => $activityC->id,
            'mode' => 'in_person',
            'start_at' => $startC,
            'end_at' => $endC,
            'location' => 'Main Hall',
        ]);

        Registration::query()->create([
            'activity_id' => $activityA->id,
            'child_id' => $childA->id,
            'status' => Registration::STATUS_CONFIRMED,
            'position' => 1,
        ]);

        Registration::query()->create([
            'activity_id' => $activityA->id,
            'child_id' => $childB->id,
            'status' => Registration::STATUS_WAITING,
            'position' => 1,
        ]);

