//
//  SetupView.m
//
//  Created by Knut Erik Borgen on 08.08.10.
//  Copyright 2010 Knubo Borgen. All rights reserved.
//

#import "SetupView.h"
#import "JSON/JSON.h"

@implementation SetupView


@synthesize domain;
@synthesize username;
@synthesize password;
@synthesize connection;

- (void)dealloc {
	
    [domain release];
    [username release];
    [password release];
	
	
    [super dealloc];
}

- (void) loadSettings {
	
	NSUserDefaults *userDefaults = [NSUserDefaults standardUserDefaults];  
	
	username.text = [userDefaults stringForKey:@"frittregnskap_username"];
	domain.text = [userDefaults stringForKey:@"frittregnskap_domain"];
	password.text = [userDefaults stringForKey:@"frittregnskap_password"];
	
}

- (void) saveSettings {
	NSUserDefaults *userDefaults = [NSUserDefaults standardUserDefaults];  
	
	[userDefaults setObject:username.text forKey:@"frittregnskap_username"];
	[userDefaults setObject:domain.text forKey:@"frittregnskap_domain"];
	[userDefaults setObject:password.text forKey:@"frittregnskap_password"];
	[userDefaults synchronize];
}

- (BOOL)textFieldShouldReturn:(UITextField *)textField {
	[textField resignFirstResponder];
	return NO;
}

- (IBAction)synchronizeDatabase:(id)sender {
	[okButton setEnabled:false];
	[synchronizeButton setEnabled:false];
	
	[activityIndicator startAnimating];
	
	
	NSRange range = [domain.text rangeOfString : @"."];
	
	if(range.location != NSNotFound) {
		label.text = @"Bruk kun prefix av domenenavn";
		return;
	}
	
	label.text = @"Henter data...";
	responseData = [[NSMutableData data] retain];
	
	NSString *uri = [NSString stringWithFormat: @"http://%@.frittregnskap.no/RegnskapServer/services/directauth/persons.php?action=changes&user=%@&password=%@",domain.text, username.text,password.text];
	
	
	NSURLRequest *request = [NSURLRequest requestWithURL:[NSURL URLWithString:uri]];
	connection = [[NSURLConnection alloc] initWithRequest:request delegate:self];
	
	
}

- (void)connection:(NSURLConnection *)connection didReceiveResponse:(NSURLResponse *)response {
	[responseData setLength:0];
}

- (void)connection:(NSURLConnection *)connection didReceiveData:(NSData *)data {
	[responseData appendData:data];
}

- (void)connection:(NSURLConnection *)connection didFailWithError:(NSError *)error {
	label.text = [NSString stringWithFormat:@"Klarte ikke koble til.", [error description]];
	[okButton setEnabled:true];
	[synchronizeButton setEnabled:true];
	[activityIndicator stopAnimating];


}

- (void)setMinMaxValues:(NSNumber *)min_semester max_semester:(NSNumber *)max_semester min_year:(NSNumber *)min_year max_year:(NSNumber *)max_year {
	NSUserDefaults *userDefaults = [NSUserDefaults standardUserDefaults];  
	
	[userDefaults setObject:min_semester forKey:@"frittregnskap.min_semester"];
	[userDefaults setObject:max_semester forKey:@"frittregnskap.max_semester"];
	[userDefaults setObject:min_year forKey:@"frittregnskap.min_year"];
	[userDefaults setObject:max_year forKey:@"frittregnskap.max_year"];
	[userDefaults synchronize];
	
}

- (void)connectionDidFinishLoading:(NSURLConnection *)con {
	[connection release];
	
	NSString *responseString = [[NSString alloc] initWithData:responseData encoding:NSUTF8StringEncoding];
	[responseData release];
	
	NSDictionary *data = [responseString JSONValue]; 
	
	[responseString release];
	
	if(data == nil) {

		label.text = @"Feil! Sjekk adresse, brukernavn og passord.";
		return;
	}
	
	NSArray *persons = [data objectForKey:@"people"];
	NSArray *semesters = [data objectForKey:@"semesters"];
	NSArray *year_memberships = [data objectForKey:@"year_memberships"];
	NSArray *youth_memberships = [data objectForKey:@"youth_memberships"];
	NSArray *train_memberships = [data objectForKey:@"train_memberships"];
	NSArray *course_memberships = [data objectForKey:@"course_memberships"];
	NSNumber *min_semester =[data objectForKey:@"min_semester"];
	NSNumber *max_semester =[data objectForKey:@"max_semester"];
	NSNumber *min_year =[data objectForKey:@"min_semester"];
	NSNumber *max_year =[data objectForKey:@"max_semester"];

	
	if (persons == nil) {
		label.text = @"Feil! Sjekk adresse, brukernavn og passord.";
		return;
		
	} 
	
	label.text = @"Sletter eksisterende data...";
	[appDelegate deleteObjectsInDatabase:@"Person"];
	[appDelegate deleteObjectsInDatabase:@"YearMembership"];
	[appDelegate deleteObjectsInDatabase:@"SemesterMembership"];
	[appDelegate deleteObjectsInDatabase:@"Semester"];

	label.text = @"Lagrer nye data...";	
	[appDelegate savePersons:persons];
	[appDelegate saveSemesters:semesters];
	[appDelegate saveYearMemberships:year_memberships];
	[appDelegate saveSemesterMemberships:youth_memberships type:@"Y"];
	[appDelegate saveSemesterMemberships:train_memberships type:@"T"];
	[appDelegate saveSemesterMemberships:course_memberships type:@"C"];
	[self setMinMaxValues:min_semester max_semester:max_semester min_year:min_year max_year:max_year];
	
	label.text = [NSString stringWithFormat:@"Antall personer innlest: %d", [persons count]];
	
	[activityIndicator stopAnimating];
	[okButton setEnabled:true];
	[synchronizeButton setEnabled:true];

}





@end
