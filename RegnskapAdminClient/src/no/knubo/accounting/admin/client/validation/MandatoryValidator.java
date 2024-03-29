package no.knubo.accounting.admin.client.validation;

public class MandatoryValidator extends ValidatorBase {

    public MandatoryValidator(String errorText) {
        super(errorText);
    }

    @Override
	protected boolean validate(Validateable val) {
        return !(val.getText() == null || val.getText().trim().length() == 0);
    }
}
