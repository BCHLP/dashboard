import Layout from '@/layouts/auth-layout'
import { Head } from '@inertiajs/react'
import Voice from '@/components/voice';

export default function VerifyVoice() {
    return (
        <Layout title={"Voice MFA"} description={"In order to continue, we need you to record a 10 second audio sample."}>
            <Head title="Voice Authentication Required"/>

            <Voice apiEndpoint={"/api/voice/compare"}></Voice>
        </Layout>
    )
}
