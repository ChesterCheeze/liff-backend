<div class="grid grid-cols-1 md:grid-cols-3 gap-8 py-4 animate-fade-in">
    <x-dashboard-card
        :icon="'<svg class=\'h-7 w-7 text-blue-200\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'2\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M12 4v16m8-8H4\'/></svg>'"
        title="Create Survey"
        description="Start a new survey for your audience."
        :link="route('survey.create')"
    />
    <x-dashboard-card
        :icon="'<svg class=\'h-7 w-7 text-green-200\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'2\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M3 7h18M3 12h18M3 17h18\'/></svg>'"
        title="View Surveys"
        description="See all your created surveys."
        link="/survey"
    />
    <x-dashboard-card
        :icon="'<svg class=\'h-7 w-7 text-purple-200\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'2\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M5.121 17.804A13.937 13.937 0 0112 15c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0z\'/></svg>'"
        title="Profile"
        description="View and edit your profile information."
        link="#"
    />
</div>
<style>
@keyframes fade-in {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: none; }
}
.animate-fade-in {
  animation: fade-in 0.7s cubic-bezier(.4,0,.2,1) both;
}
</style>
