import AltAkismet from './components/AltAkismet.vue';
import AltAkismetSubmission from './components/AltAkismetSubmission.vue';

Statamic.booting(() => {
    Statamic.$inertia.register('alt-akismet::Index', AltAkismet);
    Statamic.$inertia.register('alt-akismet::Show', AltAkismetSubmission);
});
