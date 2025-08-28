import { Fragment, useState } from 'react';
import { Button, Modal, ModalVariant, Wizard, WizardHeader, WizardStep } from '@patternfly/react-core';

interface RouterModalProps {
    isOpen: boolean;
    onClose: () => void;
}

export const RouterModal: React.FunctionComponent<RouterModalProps> = ({ isOpen, onClose }) => {
    const handleWizardToggle = () => {
        onClose();
    };

    const numberedSteps = [1, 2, 3, 4].map((stepNumber) => (
        <WizardStep name={`Step ${stepNumber}`} key={`Step ${stepNumber}`} id={`with-wizard-step-${stepNumber}`}>
            {`Step ${stepNumber}`}
        </WizardStep>
    ));

    return (
        <Modal
            variant={ModalVariant.large}
            isOpen={isOpen}
            aria-labelledby="modal-wizard-label"
            aria-describedby="modal-wizard-description"
        >
                <Wizard
                    height={400}
                    header={
                        <WizardHeader
                            title="Wizard modal"
                            titleId="modal-wizard-label"
                            description="This is a wizard inside of a modal."
                            descriptionId="modal-wizard-description"
                            onClose={handleWizardToggle}
                            closeButtonAriaLabel="Close wizard"
                        />
                    }
                    onClose={handleWizardToggle}
                >
                    {numberedSteps}
                    <WizardStep
                        name="Review"
                        id="with-wizard-review-step"
                        footer={{ nextButtonText: 'Finish', onNext: handleWizardToggle }}
                    >
                        Review step
                    </WizardStep>
                </Wizard>
        </Modal>
    );
};
