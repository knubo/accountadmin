package no.knubo.accounting.admin.client;

/**
 * Interface to represent the constants contained in resource bundle:
 * 	'/Users/knuterikborgen/kode/workspace/RegnskapAdmin/src/no/knubo/accounting/admin/client/Elements.properties'.
 */
public interface Elements extends com.google.gwt.i18n.client.Constants {
  
  /**
   * Translated "Elements".
   * 
   * @return translated "Elements"
   */
  @DefaultStringValue("Elements")
  @Key("ClassName")
  String ClassName();

  /**
   * Translated "Logg inn".
   * 
   * @return translated "Logg inn"
   */
  @DefaultStringValue("Logg inn")
  @Key("login")
  String login();

  /**
   * Translated "Passord".
   * 
   * @return translated "Passord"
   */
  @DefaultStringValue("Passord")
  @Key("password")
  String password();

  /**
   * Translated "Registrer deg".
   * 
   * @return translated "Registrer deg"
   */
  @DefaultStringValue("Registrer deg")
  @Key("signup")
  String signup();

  /**
   * Translated "Brukernavn".
   * 
   * @return translated "Brukernavn"
   */
  @DefaultStringValue("Brukernavn")
  @Key("username")
  String username();
}
