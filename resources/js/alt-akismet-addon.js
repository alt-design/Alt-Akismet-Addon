import AltAkismet from './components/AltAkismet.vue';
import AltAkismetSubmission from './AltAkismetSubmission.vue';

Statamic.booting(() => {
    Statamic.$components.register('alt-akismet', AltAkismet);
    Statamic.$components.register('alt-akismet-submission', AltAkismetSubmission);
});
