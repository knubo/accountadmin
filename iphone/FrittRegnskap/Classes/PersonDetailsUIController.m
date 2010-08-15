//
//  PersonDetailsUIController.m
//
//  Created by Knut Erik Borgen on 15.08.10.
//  Copyright 2010 __MyCompanyName__. All rights reserved.
//

#import "PersonDetailsUIController.h"

@implementation PersonDetailsUIController


- (void)viewDidLoad {
	
	[super viewDidLoad];
	
	UIScrollView *tempScrollView=(UIScrollView *)self.view;
		
	tempScrollView.pagingEnabled = false;

	CGRect frame = commentsView.frame;
	frame.size.height = commentsView.contentSize.height;
	commentsView.frame = frame;
	
	tempScrollView.contentSize=CGSizeMake(320,frame.size.height + 380);

}

- (void)showPersonDetail:(Person*) person {
	firstname.text = person.firstname;
	lastname.text = person.lastname;
	email.text = person.email;
	postnmb.text = person.postnmb;
	city.text = person.city;
	country.text = person.country;
	
	if([person.phone isEqual: @"#SECRET#"]) {
		phone.text = @"Hemmelig telefonnummer";
		cellphone.text = @"Hemmelig telefonnummer";
		address.text = @"Hemmelig adresse";
		[phone setTextColor:[UIColor redColor]];
		[cellphone setTextColor:[UIColor redColor]];
		[address setTextColor:[UIColor redColor]];
		
	} else {
		phone.text = person.phone;
		cellphone.text = person.cellphone;
		address.text = person.address;
		[phone setTextColor:[UIColor blackColor]];
		[cellphone setTextColor:[UIColor blackColor]];
		[address setTextColor:[UIColor blackColor]];
	}
	
	sex.text = person.gender;
	
	if([person.newsletter isEqual: @"1"]) {
		newsletter.text = @"Ja";
	} else {
		newsletter.text = @"Nei";
	}

	if([person.employee isEqual: @"1"]) {
		employee.text = @"Ja";
	} else {
		employee.text = @"Nei";
	}
	[comment setText:person.comment];
	
}


@end
