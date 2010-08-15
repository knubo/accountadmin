//
//  PersonDetailsUIController.h
//
//  Created by Knut Erik Borgen on 15.08.10.
//  Copyright 2010 __MyCompanyName__. All rights reserved.
//

#import <UIKit/UIKit.h>
#import <Foundation/Foundation.h>
#include "model/Person.h"

@interface PersonDetailsUIController : UIViewController {

	IBOutlet UITextView *commentsView;
	IBOutlet UITextView *firstname;
	IBOutlet UITextView *lastname;
	IBOutlet UITextView *address;
	IBOutlet UITextView *email;
	IBOutlet UITextView *postnmb;
	IBOutlet UITextView *city;
	IBOutlet UITextView *country;
	IBOutlet UITextView *sex;
	IBOutlet UITextView *newsletter;
	IBOutlet UITextView *employee;
	IBOutlet UITextView *phone;
	IBOutlet UITextView *cellphone;
	IBOutlet UITextView *comment;
}

- (void)showPersonDetail:(Person*) person;

@end
