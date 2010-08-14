//
//  SetupView.m
//
//  Created by Knut Erik Borgen on 08.08.10.
//  Copyright 2010 Knubo Borgen. All rights reserved.
//

#import "SetupView.h"
#import "JSON/JSON.h"
#import "FrittRegnskapViewController.h"
#import "model/Person.h"
#import "model/Semester.h"
#import "model/CourseMembership.h"
#import "model/YearMembership.h"

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
	label.text = [NSString stringWithFormat:@"Connection failed: %@", [error description]];
}

- (void)connectionDidFinishLoading:(NSURLConnection *)con {
	[connection release];
	
	NSString *responseString = [[NSString alloc] initWithData:responseData encoding:NSUTF8StringEncoding];
	[responseData release];
	
	NSDictionary *data = [responseString JSONValue]; 
	
	[responseString release];
	
	if(data == nil) {

		label.text = @"Klarte ikke tolke data. Sjekk adresse, brukernavn og passord.";
		return;
	}
	
	NSArray *persons = [data objectForKey:@"people"];
//	NSArray *semesters = [data objectForKey:@"semesters"];
//	NSArray *year_memberships = [data objectForKey:@"year_memberships"];
//	NSArray *youth_memberships = [data objectForKey:@"youth_memberships"];
//	NSArray *train_memberships = [data objectForKey:@"train_memberships"];
//	NSArray *course_memberships = [data objectForKey:@"course_memberships"];
	
	if (persons == nil) {
		label.text = @"Klarte ikke tolke data. Sjekk adresse, brukernavn og passord.";
		return;
		
	} 
	
	[appDelegate savePersons:persons];
	
	
	label.text = [NSString stringWithFormat:@"Antall personer innlest: %d", [persons count]];
	
}





@end
