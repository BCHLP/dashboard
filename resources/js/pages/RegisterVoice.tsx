import Voice from '@/components/voice';
import Layout from '@/layouts/auth-layout';
import { Head } from '@inertiajs/react';

export default function RegisterVoice({}) {
    return (
        <Layout title={'Voice MFA'} description={'In order to continue, we need you to record a 10 second audio sample.'}>
            <Head title="Register Voice" />

            <Voice apiEndpoint={'/api/voice/register'}></Voice>
        </Layout>
    );
}
